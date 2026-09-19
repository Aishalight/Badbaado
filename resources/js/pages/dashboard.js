import { Chart, ArcElement, BarElement, CategoryScale, DoughnutController, Filler, Legend, LineController, LineElement, LinearScale, PointElement, Tooltip } from 'chart.js';

Chart.register(ArcElement, BarElement, CategoryScale, DoughnutController, Filler, Legend, LineController, LineElement, LinearScale, PointElement, Tooltip);

const readChartData = (element) => JSON.parse(element.dataset.chart);

const themeColors = () => {
    const styles = getComputedStyle(document.documentElement);

    return {
        ink: styles.getPropertyValue('--m-ink').trim(),
        muted: styles.getPropertyValue('--m-muted').trim(),
        border: styles.getPropertyValue('--m-border').trim(),
        accent: styles.getPropertyValue('--m-accent').trim(),
        blue: styles.getPropertyValue('--m-blue').trim(),
        red: styles.getPropertyValue('--m-red-rgb').trim(),
        tooltip: styles.getPropertyValue('--m-panel-2').trim(),
    };
};

const baseOptions = (colors) => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            displayColors: false,
            backgroundColor: colors.tooltip,
            titleColor: colors.muted,
            bodyColor: colors.ink,
            padding: 12,
            cornerRadius: 8,
            titleFont: { weight: '700', size: 12 },
            bodyFont: { size: 12 },
            bodySpacing: 4,
        },
    },
});

const initTrendChart = (element, colors) => {
    const data = readChartData(element);

    return new Chart(element, {
        type: 'line',
        data: {
            labels: data.map((item) => item.label),
            datasets: [{
                data: data.map((item) => item.count),
                borderColor: colors.accent,
                backgroundColor: (context) => {
                    const chart = context.chart;
                    const { ctx, chartArea } = chart;
                    if (!chartArea) return `color-mix(in srgb, ${colors.accent} 16%, transparent)`;
                    const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                    gradient.addColorStop(0, `color-mix(in srgb, ${colors.accent} 22%, transparent)`);
                    gradient.addColorStop(0.7, `color-mix(in srgb, ${colors.accent} 6%, transparent)`);
                    gradient.addColorStop(1, `color-mix(in srgb, ${colors.accent} 0%, transparent)`);
                    return gradient;
                },
                borderWidth: 2.5,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointBackgroundColor: colors.accent,
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                fill: true,
                tension: 0.38,
            }],
        },
        options: {
            ...baseOptions(colors),
            scales: {
                x: { grid: { display: false }, ticks: { color: colors.muted, font: { size: 11 } } },
                y: { beginAtZero: true, border: { display: false }, grid: { color: colors.border }, ticks: { color: colors.muted, precision: 0, font: { size: 11 } } },
            },
        },
    });
};

const initStatusChart = (element, colors) => {
    const data = readChartData(element);
    const palette = [colors.accent, colors.blue, '#f59e0b', '#34d399', '#fb7185', '#94a3b8'];

    return new Chart(element, {
        type: 'doughnut',
        data: {
            labels: data.map((item) => item.label),
            datasets: [{
                data: data.map((item) => item.count),
                backgroundColor: palette,
                borderColor: colors.ink,
                borderWidth: 3,
                hoverOffset: 5,
                hoverBorderColor: '#fff',
                hoverBorderWidth: 2,
            }],
        },
        options: {
            ...baseOptions(colors),
            cutout: '72%',
            plugins: { ...baseOptions(colors).plugins, legend: { display: false } },
        },
    });
};

let activeCharts = [];

const renderDashboardCharts = () => {
    const trend = document.querySelector('[data-dashboard-trend]');
    const status = document.querySelector('[data-dashboard-status]');
    activeCharts.forEach((chart) => chart.destroy());
    activeCharts = [];
    if (!trend && !status) return;

    const colors = themeColors();
    if (trend) activeCharts.push(initTrendChart(trend, colors));
    if (status) activeCharts.push(initStatusChart(status, colors));
};

export const initDashboardCharts = () => {
    renderDashboardCharts();
    window.addEventListener('badbaado:theme-change', renderDashboardCharts);
};
