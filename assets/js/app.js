document.addEventListener('DOMContentLoaded', () => {
  const year = document.querySelector('[data-current-year]');
  if (year) year.textContent = String(new Date().getFullYear());

  const chartColors = ['#f0ad4e', '#ff2525', '#ff7043', '#58a6ff', '#a78bfa', '#777777'];
  const resizeCanvas = (canvas, height) => {
    const ratio = window.devicePixelRatio || 1;
    const width = canvas.clientWidth;
    canvas.width = Math.round(width * ratio);
    canvas.height = Math.round(height * ratio);
    const context = canvas.getContext('2d');
    context.scale(ratio, ratio);
    return { context, width, height };
  };

  const salesCanvas = document.querySelector('#sales-chart');
  const salesDataElement = document.querySelector('#sales-chart-data');
  if (salesCanvas && salesDataElement) {
    const salesData = JSON.parse(salesDataElement.textContent);
    const drawSales = () => {
      const { context, width, height } = resizeCanvas(salesCanvas, 260);
      const padding = { top: 18, right: 12, bottom: 34, left: 55 };
      const chartWidth = width - padding.left - padding.right;
      const chartHeight = height - padding.top - padding.bottom;
      const values = salesData.values.map(Number);
      const maxValue = Math.max(...values, 1);
      const formatValue = new Intl.NumberFormat('es-CO', { notation: 'compact', maximumFractionDigits: 1 });

      context.font = '11px Manrope, Arial, sans-serif';
      context.textAlign = 'right';
      context.textBaseline = 'middle';
      for (let step = 0; step <= 4; step += 1) {
        const y = padding.top + chartHeight * step / 4;
        context.strokeStyle = 'rgba(255,255,255,.1)';
        context.beginPath();
        context.moveTo(padding.left, y);
        context.lineTo(width - padding.right, y);
        context.stroke();
        context.fillStyle = '#aaa';
        context.fillText(formatValue.format(maxValue * (4 - step) / 4), padding.left - 8, y);
      }

      const points = values.map((value, index) => ({
        x: padding.left + (values.length === 1 ? chartWidth / 2 : chartWidth * index / (values.length - 1)),
        y: padding.top + chartHeight - chartHeight * value / maxValue,
      }));
      context.strokeStyle = '#ff2525';
      context.lineWidth = 3;
      context.beginPath();
      points.forEach((point, index) => {
        if (index === 0) context.moveTo(point.x, point.y);
        else context.lineTo(point.x, point.y);
      });
      context.stroke();
      points.forEach((point, index) => {
        context.fillStyle = '#ff2525';
        context.beginPath();
        context.arc(point.x, point.y, 4, 0, Math.PI * 2);
        context.fill();
        context.fillStyle = '#aaa';
        context.textAlign = 'center';
        context.textBaseline = 'top';
        context.fillText(salesData.labels[index], point.x, height - padding.bottom + 12);
      });
    };
    drawSales();
    window.addEventListener('resize', drawSales);
  }

  const ordersCanvas = document.querySelector('#orders-chart');
  const ordersDataElement = document.querySelector('#orders-chart-data');
  if (ordersCanvas && ordersDataElement) {
    const ordersData = JSON.parse(ordersDataElement.textContent);
    const drawOrders = () => {
      const { context, width, height } = resizeCanvas(ordersCanvas, 190);
      const values = ordersData.values.map(Number);
      const total = values.reduce((sum, value) => sum + value, 0);
      const centerX = width / 2;
      const centerY = height / 2;
      const radius = Math.min(width / 2 - 12, height / 2 - 12);
      let startAngle = -Math.PI / 2;

      values.forEach((value, index) => {
        if (value === 0 || total === 0) return;
        const endAngle = startAngle + value / total * Math.PI * 2;
        context.beginPath();
        context.arc(centerX, centerY, radius, startAngle, endAngle);
        context.arc(centerX, centerY, radius * .63, endAngle, startAngle, true);
        context.closePath();
        context.fillStyle = chartColors[index];
        context.fill();
        startAngle = endAngle;
      });
      context.textAlign = 'center';
      context.textBaseline = 'middle';
      context.fillStyle = '#fff';
      context.font = '800 24px Manrope, Arial, sans-serif';
      context.fillText(String(total), centerX, centerY - 5);
      context.fillStyle = '#aaa';
      context.font = '11px Manrope, Arial, sans-serif';
      context.fillText('pedidos', centerX, centerY + 15);
    };
    drawOrders();
    window.addEventListener('resize', drawOrders);
  }

  document.querySelectorAll('form[data-validate]').forEach((form) => {
    form.addEventListener('submit', (event) => {
      if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      form.classList.add('was-validated');
    });
  });
});
