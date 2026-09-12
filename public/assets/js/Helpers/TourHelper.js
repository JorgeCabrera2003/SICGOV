export class TourHelper {
  constructor(config = {}) {
    this.steps = config.steps || [];
    this.driverObj = null;
    this.config = config;
  }

  async init() {
    await this.loadDependencies();
    
    const driver = window.driver.js.driver;

    this.driverObj = driver({
      showProgress: true,
      animate: true,
      steps: this.steps,
      nextBtnText: 'Siguiente',
      prevBtnText: 'Anterior',
      doneBtnText: 'Finalizar',
      closeBtnText: 'Cerrar',
      ...this.config
    });
  }

  start() {
    if (this.driverObj) {
      this.driverObj.drive();
    } else {
      console.error("TourHelper: El driver no está inicializado. Asegúrate de llamar a init() asíncronamente primero.");
    }
  }

  loadDependencies() {
    return new Promise((resolve) => {
      let cssLoaded = !!document.getElementById('driver-css');
      let jsLoaded = !!document.getElementById('driver-js');

      if (cssLoaded && jsLoaded) {
        return resolve();
      }

      const checkLoaded = () => {
        if (document.getElementById('driver-css') && window.driver) {
          resolve();
        }
      };

      if (!cssLoaded) {
        const link = document.createElement('link');
        link.id = 'driver-css';
        link.rel = 'stylesheet';
        link.href = `${BASE_URL}/assets/css/lib/driver.css`;
        link.onload = checkLoaded;
        document.head.appendChild(link);
      }

      if (!jsLoaded) {
        const script = document.createElement('script');
        script.id = 'driver-js';
        script.src = `${BASE_URL}/assets/js/lib/driver.js.iife.js`;
        script.onload = checkLoaded;
        document.head.appendChild(script);
      }
    });
  }
}
