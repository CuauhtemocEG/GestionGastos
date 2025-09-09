// Script para corregir problemas de expansión de gráficos Chart.js
// Configuración global para Chart.js

// Configuración global por defecto
Chart.defaults.responsive = true;
Chart.defaults.maintainAspectRatio = false;
Chart.defaults.interaction = {
    intersect: false,
    mode: 'index'
};

// Plugin global para controlar el tamaño de los gráficos
Chart.register({
    id: 'sizeController',
    beforeInit: function(chart) {
        // Asegurar que el contenedor tenga un tamaño definido
        const canvas = chart.canvas;
        const parent = canvas.parentElement;
        
        if (parent && !parent.style.position) {
            parent.style.position = 'relative';
        }
        
        if (parent && !parent.style.height) {
            parent.style.height = '300px';
        }
    },
    resize: function(chart) {
        // Limitar el tamaño máximo durante el resize
        const maxHeight = 400;
        const maxWidth = 800;
        
        if (chart.height > maxHeight) {
            chart.height = maxHeight;
        }
        
        if (chart.width > maxWidth) {
            chart.width = maxWidth;
        }
    }
});

// Función helper para crear gráficos con configuración segura
function createSafeChart(canvasId, config) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) {
        console.warn(`Canvas con ID '${canvasId}' no encontrado`);
        return null;
    }
    
    // Asegurar configuraciones por defecto
    if (!config.options) {
        config.options = {};
    }
    
    config.options.responsive = true;
    config.options.maintainAspectRatio = false;
    
    // Configurar plugins por defecto
    if (!config.options.plugins) {
        config.options.plugins = {};
    }
    
    // Configurar escalas por defecto
    if (!config.options.scales) {
        config.options.scales = {};
    }
    
    try {
        return new Chart(canvas.getContext('2d'), config);
    } catch (error) {
        console.error(`Error creando gráfico '${canvasId}':`, error);
        return null;
    }
}

// Función para redimensionar todos los gráficos activos
function resizeAllCharts() {
    Chart.instances.forEach(function(chart) {
        if (chart && typeof chart.resize === 'function') {
            chart.resize();
        }
    });
}

// Event listener para redimensionar ventana
window.addEventListener('resize', function() {
    clearTimeout(window.resizeTimeout);
    window.resizeTimeout = setTimeout(resizeAllCharts, 100);
});

// Configuraciones por defecto para diferentes tipos de gráficos
const chartDefaults = {
    line: {
        tension: 0.4,
        pointRadius: 4,
        pointHoverRadius: 6,
        borderWidth: 2
    },
    bar: {
        borderWidth: 1,
        borderRadius: 4
    },
    doughnut: {
        cutout: '60%',
        borderWidth: 2
    },
    pie: {
        borderWidth: 2
    }
};

// Aplicar configuraciones por defecto
Object.keys(chartDefaults).forEach(type => {
    if (Chart.defaults[type]) {
        Object.assign(Chart.defaults[type], chartDefaults[type]);
    }
});

// Función para aplicar colores consistentes
function getConsistentColors(count) {
    const colors = [
        '#3B82F6', // blue-500
        '#EF4444', // red-500
        '#10B981', // green-500
        '#F59E0B', // yellow-500
        '#8B5CF6', // purple-500
        '#06B6D4', // cyan-500
        '#84CC16', // lime-500
        '#F97316', // orange-500
        '#EC4899', // pink-500
        '#6B7280'  // gray-500
    ];
    
    const result = [];
    for (let i = 0; i < count; i++) {
        result.push(colors[i % colors.length]);
    }
    return result;
}

// Función para formatear números como moneda
function formatCurrency(value) {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN'
    }).format(value);
}

// Función para formatear porcentajes
function formatPercentage(value) {
    return value.toFixed(1) + '%';
}

// Debug: Contar gráficos activos
function debugChartCount() {
    console.log('Gráficos activos:', Chart.instances.length);
    Chart.instances.forEach((chart, index) => {
        if (chart && chart.canvas) {
            console.log(`Gráfico ${index}:`, chart.canvas.id, 'Tamaño:', chart.width + 'x' + chart.height);
        }
    });
}

// Función para destruir todos los gráficos (útil para debugging)
function destroyAllCharts() {
    Chart.instances.forEach(function(chart) {
        if (chart && typeof chart.destroy === 'function') {
            chart.destroy();
        }
    });
    console.log('Todos los gráficos han sido destruidos');
}

console.log('Chart.js fix script cargado correctamente');
