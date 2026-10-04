import { Chart, ArcElement, BarElement, CategoryScale, DoughnutController, Filler, Legend, LineController, LineElement, LinearScale, PointElement, Tooltip } from 'chart.js';

Chart.register(ArcElement, BarElement, CategoryScale, DoughnutController, Filler, Legend, LineController, LineElement, LinearScale, PointElement, Tooltip);

const readChartData = (element) => JSON.parse(element.dataset.chart);

const themeColors = () => {
    const styles = getComputedStyle(document.documentElement);

    return {
        ink: styles.getPropertyValue('--m-ink').trim(),
        muted: styles.getPropertyValue('--m-muted').trim(),
        faint: styles.getPropertyValue('--m-faint').trim(),
        border: styles.getPropertyValue('--m-border').trim(),
        surface: styles.getPropertyValue('--m-card-solid').trim(),
        accent: styles.getPropertyValue('--m-accent').trim(),
        blue: styles.getPropertyValue('--m-blue').trim(),
        red: styles.getPropertyValue('--m-red-rgb').trim(),
        tooltip: styles.getPropertyValue('--m-chrome').trim(),
    };
};

const baseOptions = (colors) => ({
    responsive: true,
    maintainAspectRatio: false,
    animation: { duration: 320, easing: 'easeOutQuart' },
    plugins: {
        legend: { display: false },
        tooltip: {
            displayColors: false,
            backgroundColor: colors.tooltip,
            titleColor: colors.muted,
            bodyColor: colors.ink,
            borderColor: colors.border,
            borderWidth: 1,
            padding: 10,
            cornerRadius: 6,
            titleFont: { weight: '600', size: 11 },
            bodyFont: { size: 12 },
            bodySpacing: 4,
            caretSize: 4,
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
                    if (!chartArea) return `color-mix(in srgb, ${colors.accent} 14%, transparent)`;
                    const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                    gradient.addColorStop(0, `color-mix(in srgb, ${colors.accent} 16%, transparent)`);
                    gradient.addColorStop(1, `color-mix(in srgb, ${colors.accent} 0%, transparent)`);
                    return gradient;
                },
                borderWidth: 2,
                borderCapStyle: 'round',
                borderJoinStyle: 'round',
                pointRadius: 0,
                pointHoverRadius: 4,
                pointHitRadius: 16,
                pointBackgroundColor: colors.accent,
                pointBorderColor: colors.surface,
                pointBorderWidth: 2,
                fill: true,
                tension: 0.35,
            }],
        },
        options: {
            ...baseOptions(colors),
            interaction: { mode: 'index', intersect: false },
            scales: {
                x: {
                    grid: { display: false },
                    border: { display: false },
                    ticks: { color: colors.faint, font: { size: 11 }, maxRotation: 0, autoSkipPadding: 16 },
                },
                y: {
                    beginAtZero: true,
                    border: { display: false },
                    grid: { color: colors.border, drawTicks: false },
                    ticks: { color: colors.faint, precision: 0, font: { size: 11 }, padding: 8 },
                },
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
                /* A hairline in the surface colour separates the segments.
                   A thick contrasting border is what reads as "generated". */
                borderColor: colors.surface,
                borderWidth: 1,
                hoverOffset: 4,
                hoverBorderColor: colors.surface,
                hoverBorderWidth: 1,
            }],
        },
        options: {
            ...baseOptions(colors),
            cutout: '76%',
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
