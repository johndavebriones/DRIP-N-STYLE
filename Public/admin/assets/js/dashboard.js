
const yellowColors = {
  dark: '#F59E0B',    
  medium: '#FBBF24',    
  light: '#FCD34D',    
  lighter: '#FDE68A',  
  lightest: '#FEF3C7', 
  array: ['#F59E0B', '#FBBF24', '#FCD34D', '#FDE68A', '#FEF3C7', '#D97706', '#B45309', '#92400E']
};

const commonOptions = {
  responsive: true,
  maintainAspectRatio: false
};

document.addEventListener('DOMContentLoaded', function() {
  initRevenueChart();
  initTopProductsChart();
  initCategoryChart();
  initOrderTrendsChart();
  initOrderStatusChart();
  initOrderCategoryChart();
  initTurnoverChart();
  initSizesChart();
  initColorsChart();
});

// Revenue Trends Line Chart
function initRevenueChart() {
  const ctx = document.getElementById('revenueChart');
  if (!ctx) return;

  new Chart(ctx.getContext('2d'), {
    type: 'line',
    data: {
      labels: chartData.trendDates,
      datasets: [{
        label: 'Revenue (₱)',
        data: chartData.trendRevenues,
        borderColor: yellowColors.dark,
        backgroundColor: yellowColors.lightest,
        tension: 0.4,
        fill: true,
        borderWidth: 3
      }]
    },
    options: {
      ...commonOptions,
      plugins: {
        legend: {
          display: true,
          position: 'top'
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: function(value) {
              return '₱' + value.toLocaleString();
            }
          }
        }
      }
    }
  });
}

function initTopProductsChart() {
  const ctx = document.getElementById('topProductsChart');
  if (!ctx) return;

  new Chart(ctx.getContext('2d'), {
    type: 'bar',
    data: {
      labels: chartData.productNames,
      datasets: [{
        label: 'Quantity Sold',
        data: chartData.productQuantities,
        backgroundColor: yellowColors.array,
        borderColor: yellowColors.dark,
        borderWidth: 1
      }]
    },
    options: {
      ...commonOptions,
      plugins: {
        legend: {
          display: false
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            stepSize: 1
          }
        }
      }
    }
  });
}

function initCategoryChart() {
  const ctx = document.getElementById('categoryChart');
  if (!ctx) return;

  new Chart(ctx.getContext('2d'), {
    type: 'pie',
    data: {
      labels: chartData.categoryNames,
      datasets: [{
        data: chartData.categorySales,
        backgroundColor: yellowColors.array,
        borderColor: '#fff',
        borderWidth: 2
      }]
    },
    options: {
      ...commonOptions,
      plugins: {
        legend: {
          position: 'bottom'
        },
        tooltip: {
          callbacks: {
            label: function(context) {
              return context.label + ': ₱' + context.parsed.toLocaleString();
            }
          }
        }
      }
    }
  });
}

// Order Trends Line Chart
function initOrderTrendsChart() {
  const ctx = document.getElementById('orderTrendsChart');
  if (!ctx) return;

  new Chart(ctx.getContext('2d'), {
    type: 'line',
    data: {
      labels: chartData.orderTrendDates,
      datasets: [{
        label: 'Number of Orders',
        data: chartData.orderTrendCounts,
        borderColor: yellowColors.dark,
        backgroundColor: yellowColors.lightest,
        tension: 0.4,
        fill: true,
        borderWidth: 3
      }]
    },
    options: {
      ...commonOptions,
      plugins: {
        legend: {
          display: true,
          position: 'top'
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            stepSize: 1
          }
        }
      }
    }
  });
}

// Orders by Status Pie Chart
function initOrderStatusChart() {
  const ctx = document.getElementById('orderStatusChart');
  if (!ctx) return;

  new Chart(ctx.getContext('2d'), {
    type: 'pie',
    data: {
      labels: chartData.orderStatuses,
      datasets: [{
        data: chartData.orderStatusCounts,
        backgroundColor: yellowColors.array,
        borderColor: '#fff',
        borderWidth: 2
      }]
    },
    options: {
      ...commonOptions,
      plugins: {
        legend: {
          position: 'bottom'
        },
        tooltip: {
          callbacks: {
            label: function(context) {
              const total = context.dataset.data.reduce((a, b) => a + b, 0);
              const percentage = ((context.parsed / total) * 100).toFixed(1);
              return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
            }
          }
        }
      }
    }
  });
}

// Orders by Category Bar Chart
function initOrderCategoryChart() {
  const ctx = document.getElementById('orderCategoryChart');
  if (!ctx) return;

  new Chart(ctx.getContext('2d'), {
    type: 'bar',
    data: {
      labels: chartData.orderCategoryNames,
      datasets: [{
        label: 'Number of Orders',
        data: chartData.orderCategoryCounts,
        backgroundColor: yellowColors.array,
        borderColor: yellowColors.dark,
        borderWidth: 1
      }]
    },
    options: {
      ...commonOptions,
      plugins: {
        legend: {
          display: false
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            stepSize: 1
          }
        }
      }
    }
  });
}

function initTurnoverChart() {
  const ctx = document.getElementById('turnoverChart');
  if (!ctx) return;

  new Chart(ctx.getContext('2d'), {
    type: 'bar',
    data: {
      labels: chartData.turnoverProductNames,
      datasets: [{
        label: 'Turnover Rate',
        data: chartData.turnoverRates,
        backgroundColor: yellowColors.array,
        borderColor: yellowColors.dark,
        borderWidth: 1
      }]
    },
    options: {
      ...commonOptions,
      plugins: {
        legend: {
          display: false
        },
        tooltip: {
          callbacks: {
            label: function(context) {
              return 'Turnover: ' + context.parsed.y + 'x';
            }
          }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          title: {
            display: true,
            text: 'Turnover Rate (Times Sold)'
          }
        }
      }
    }
  });
}

function initSizesChart() {
  const ctx = document.getElementById('sizesChart');
  if (!ctx) return;

  new Chart(ctx.getContext('2d'), {
    type: 'doughnut',
    data: {
      labels: chartData.sizeNames,
      datasets: [{
        data: chartData.sizeQuantities,
        backgroundColor: yellowColors.array,
        borderColor: '#fff',
        borderWidth: 3
      }]
    },
    options: {
      ...commonOptions,
      plugins: {
        legend: {
          position: 'bottom'
        },
        tooltip: {
          callbacks: {
            label: function(context) {
              const total = context.dataset.data.reduce((a, b) => a + b, 0);
              const percentage = ((context.parsed / total) * 100).toFixed(1);
              return context.label + ': ' + context.parsed + ' units (' + percentage + '%)';
            }
          }
        }
      }
    }
  });
}

function initColorsChart() {
  const ctx = document.getElementById('colorsChart');
  if (!ctx) return;

  new Chart(ctx.getContext('2d'), {
    type: 'bar',
    data: {
      labels: chartData.colorNames,
      datasets: [{
        label: 'Quantity Sold',
        data: chartData.colorQuantities,
        backgroundColor: yellowColors.array,
        borderColor: yellowColors.dark,
        borderWidth: 1
      }]
    },
    options: {
      ...commonOptions,
      plugins: {
        legend: {
          display: false
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            stepSize: 1
          },
          title: {
            display: true,
            text: 'Total Quantity Sold'
          }
        }
      }
    }
  });
}