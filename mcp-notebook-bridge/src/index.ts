#!/usr/bin/env node
import { Server } from "@modelcontextprotocol/sdk/server/index.js";
import { StdioServerTransport } from "@modelcontextprotocol/sdk/server/stdio.js";
import {
  CallToolRequestSchema,
  ListToolsRequestSchema,
} from "@modelcontextprotocol/sdk/types.js";
import { z } from "zod";
import * as fs from "fs/promises";
import * as path from "path";

// Helper schemas
const ReadNotebookSchema = z.object({
  filePath: z.string().describe("Ruta absoluta o relativa al archivo Jupyter Notebook (.ipynb)"),
  includeOutputs: z.boolean().optional().default(true).describe("Indica si se deben incluir las salidas de ejecución de las celdas"),
  maxOutputLength: z.number().optional().default(2000).describe("Longitud máxima de caracteres por salida para evitar saturar el contexto"),
});

const GetOutlineSchema = z.object({
  filePath: z.string().describe("Ruta al archivo .ipynb para obtener resumen de celdas"),
});

const EditCellSchema = z.object({
  filePath: z.string().describe("Ruta al archivo .ipynb"),
  cellIndex: z.number().int().nonnegative().describe("Índice de la celda a editar (0-indexed)"),
  newSource: z.string().describe("Nuevo código o contenido Markdown para la celda"),
});

const AddCellSchema = z.object({
  filePath: z.string().describe("Ruta al archivo .ipynb"),
  cellType: z.enum(["code", "markdown"]).describe("Tipo de celda: 'code' o 'markdown'"),
  source: z.string().describe("Contenido de la celda a añadir"),
  insertAtIndex: z.number().int().nonnegative().optional().describe("Posición donde insertar la celda. Si no se especifica, se añade al final"),
});

function formatSource(source: unknown): string {
  if (Array.isArray(source)) {
    return source.join("");
  }
  return typeof source === "string" ? source : "";
}

function formatOutputs(outputs: any[], maxLength: number): string[] {
  if (!Array.isArray(outputs)) return [];
  const result: string[] = [];

  for (const out of outputs) {
    let text = "";
    if (out.text) {
      text = Array.isArray(out.text) ? out.text.join("") : String(out.text);
    } else if (out.data && out.data["text/plain"]) {
      const plain = out.data["text/plain"];
      text = Array.isArray(plain) ? plain.join("") : String(plain);
    } else if (out.ename && out.evalue) {
      text = `[Error ${out.ename}]: ${out.evalue}`;
    }

    if (text) {
      if (text.length > maxLength) {
        text = text.substring(0, maxLength) + `... [truncado a ${maxLength} caracteres]`;
      }
      result.push(text);
    }
  }

  return result;
}

// Configuración del servidor MCP
const server = new Server(
  {
    name: "mcp-notebook-bridge",
    version: "1.0.0",
  },
  {
    capabilities: {
      tools: {},
    },
  }
);

// 1. Catálogo de herramientas disponibles
server.setRequestHandler(ListToolsRequestSchema, async () => {
  return {
    tools: [
      {
        name: "read_notebook_cells",
        description: "Lee y extrae de forma estructurada todas las celdas (código, markdown y salidas de ejecución) de un archivo Jupyter Notebook (.ipynb)",
        inputSchema: {
          type: "object",
          properties: {
            filePath: {
              type: "string",
              description: "Ruta del archivo .ipynb",
            },
            includeOutputs: {
              type: "boolean",
              description: "¿Incluir salidas de ejecución (stdout, display, errores)? Por defecto true",
            },
            maxOutputLength: {
              type: "number",
              description: "Límite de caracteres por salida (por defecto 2000)",
            },
          },
          required: ["filePath"],
        },
      },
      {
        name: "get_notebook_outline",
        description: "Obtiene un índice o esquema rápido del notebook (tipo de celda y vista previa de la primera línea) para inspección rápida",
        inputSchema: {
          type: "object",
          properties: {
            filePath: {
              type: "string",
              description: "Ruta del archivo .ipynb",
            },
          },
          required: ["filePath"],
        },
      },
      {
        name: "edit_notebook_cell",
        description: "Edita el contenido de una celda específica en un archivo Jupyter Notebook (.ipynb)",
        inputSchema: {
          type: "object",
          properties: {
            filePath: {
              type: "string",
              description: "Ruta del archivo .ipynb",
            },
            cellIndex: {
              type: "number",
              description: "Índice de la celda a modificar (iniciando en 0)",
            },
            newSource: {
              type: "string",
              description: "Nuevo código o contenido para la celda",
            },
          },
          required: ["filePath", "cellIndex", "newSource"],
        },
      },
      {
        name: "add_notebook_cell",
        description: "Agrega una nueva celda de código o markdown a un archivo Jupyter Notebook (.ipynb)",
        inputSchema: {
          type: "object",
          properties: {
            filePath: {
              type: "string",
              description: "Ruta del archivo .ipynb",
            },
            cellType: {
              type: "string",
              enum: ["code", "markdown"],
              description: "Tipo de celda",
            },
            source: {
              type: "string",
              description: "Contenido de la celda a insertar",
            },
            insertAtIndex: {
              type: "number",
              description: "Índice donde insertar la celda (opcional, al final por defecto)",
            },
          },
          required: ["filePath", "cellType", "source"],
        },
      },
    ],
  };
});

// 2. Ejecutor de las herramientas
server.setRequestHandler(CallToolRequestSchema, async (request) => {
  const { name, arguments: args } = request.params;

  try {
    if (name === "read_notebook_cells") {
      const parsed = ReadNotebookSchema.safeParse(args);
      if (!parsed.success) {
        throw new Error(`Parámetros inválidos: ${parsed.error.message}`);
      }

      const absolutePath = path.resolve(parsed.data.filePath);
      const raw = await fs.readFile(absolutePath, "utf-8");
      const notebook = JSON.parse(raw);

      if (!Array.isArray(notebook.cells)) {
        throw new Error("El archivo no tiene el formato estándar de Jupyter Notebook (falta arreglo 'cells')");
      }

      const summary = notebook.cells.map((cell: any, index: number) => {
        const item: Record<string, any> = {
          index,
          cell_type: cell.cell_type,
          source: formatSource(cell.source),
        };

        if (cell.cell_type === "code") {
          item.execution_count = cell.execution_count ?? null;
          if (parsed.data.includeOutputs) {
            const outs = formatOutputs(cell.outputs, parsed.data.maxOutputLength);
            if (outs.length > 0) {
              item.outputs = outs;
            }
          }
        }

        return item;
      });

      return {
        content: [
          {
            type: "text",
            text: JSON.stringify(summary, null, 2),
          },
        ],
      };
    }

    if (name === "get_notebook_outline") {
      const parsed = GetOutlineSchema.safeParse(args);
      if (!parsed.success) {
        throw new Error(`Parámetros inválidos: ${parsed.error.message}`);
      }

      const absolutePath = path.resolve(parsed.data.filePath);
      const raw = await fs.readFile(absolutePath, "utf-8");
      const notebook = JSON.parse(raw);

      const outline = (notebook.cells || []).map((cell: any, index: number) => {
        const full = formatSource(cell.source);
        const firstLine = full.split("\n")[0] || "";
        return {
          index,
          type: cell.cell_type,
          preview: firstLine.length > 80 ? firstLine.substring(0, 77) + "..." : firstLine,
          lines: full.split("\n").length,
        };
      });

      return {
        content: [
          {
            type: "text",
            text: JSON.stringify(outline, null, 2),
          },
        ],
      };
    }

    if (name === "edit_notebook_cell") {
      const parsed = EditCellSchema.safeParse(args);
      if (!parsed.success) {
        throw new Error(`Parámetros inválidos: ${parsed.error.message}`);
      }

      const absolutePath = path.resolve(parsed.data.filePath);
      const raw = await fs.readFile(absolutePath, "utf-8");
      const notebook = JSON.parse(raw);

      if (!notebook.cells || !notebook.cells[parsed.data.cellIndex]) {
        throw new Error(`No existe la celda en el índice ${parsed.data.cellIndex} (total: ${notebook.cells?.length || 0})`);
      }

      // Convertir el nuevo código a formato de array de líneas estándar en Jupyter
      const lines = parsed.data.newSource.split("\n").map((line, i, arr) => (i < arr.length - 1 ? line + "\n" : line));
      notebook.cells[parsed.data.cellIndex].source = lines;

      await fs.writeFile(absolutePath, JSON.stringify(notebook, null, 2), "utf-8");

      return {
        content: [
          {
            type: "text",
            text: `Celda [${parsed.data.cellIndex}] actualizada exitosamente en ${path.basename(absolutePath)}.`,
          },
        ],
      };
    }

    if (name === "add_notebook_cell") {
      const parsed = AddCellSchema.safeParse(args);
      if (!parsed.success) {
        throw new Error(`Parámetros inválidos: ${parsed.error.message}`);
      }

      const absolutePath = path.resolve(parsed.data.filePath);
      const raw = await fs.readFile(absolutePath, "utf-8");
      const notebook = JSON.parse(raw);

      if (!Array.isArray(notebook.cells)) {
        notebook.cells = [];
      }

      const lines = parsed.data.source.split("\n").map((line, i, arr) => (i < arr.length - 1 ? line + "\n" : line));

      const newCell: Record<string, any> = {
        cell_type: parsed.data.cellType,
        metadata: {},
        source: lines,
      };

      if (parsed.data.cellType === "code") {
        newCell.execution_count = null;
        newCell.outputs = [];
      }

      const targetIndex = parsed.data.insertAtIndex !== undefined ? parsed.data.insertAtIndex : notebook.cells.length;
      notebook.cells.splice(targetIndex, 0, newCell);

      await fs.writeFile(absolutePath, JSON.stringify(notebook, null, 2), "utf-8");

      return {
        content: [
          {
            type: "text",
            text: `Nueva celda de tipo '${parsed.data.cellType}' insertada en el índice [${targetIndex}] de ${path.basename(absolutePath)}. Total celdas: ${notebook.cells.length}.`,
          },
        ],
      };
    }

    throw new Error(`Herramienta no reconocida: ${name}`);
  } catch (error: any) {
    return {
      content: [
        {
          type: "text",
          text: `Error ejecutando ${name}: ${error.message || String(error)}`,
        },
      ],
      isError: true,
    };
  }
});

// Inicio del servidor usando Stdio
async function main() {
  const transport = new StdioServerTransport();
  await server.connect(transport);
  console.error("MCP Notebook Bridge Server activo y escuchando en stdio");
}

main().catch((error) => {
  console.error("Error fatal en main():", error);
  process.exit(1);
});
