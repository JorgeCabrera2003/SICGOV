INSERT INTO permiso (id_permiso, id_rol, id_modulo, accion, estatus)
SELECT 'ROLS7HVRTD2026112600000001', r.id_rol, m.id_modulo, 'ver_todos', 1
FROM rol r
INNER JOIN modulo m ON m.nombre = 'horario'
WHERE r.id_rol = 'ROLS71920260602140652719'
ON DUPLICATE KEY UPDATE estatus = VALUES(estatus);
