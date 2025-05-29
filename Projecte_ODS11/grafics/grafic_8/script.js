// Global variables
let allData = [];
let filteredData = [];
let chart;

// Chart color palette
const COLORS = {
    primary: '#2d7d32',
    secondary: '#4caf50',
    accent: '#1976d2',
    warning: '#ff9800',
    critical: '#f44336',
    light: '#c8e6c9',
    neutral: '#6c757d'
};

// Initialize the application
document.addEventListener('DOMContentLoaded', function() {
    loadData();
    setupEventListeners();
});

// Setup event listeners
function setupEventListeners() {
    document.getElementById('apply-filters').addEventListener('click', applyFilters);
    document.getElementById('year-filter').addEventListener('change', applyFilters);
}

// Load data from API
async function loadData() {
    try {
        showLoading();
        
        // const response = await fetch('../../api/residus.php');
         const response = await fetch('https://analisi.transparenciacatalunya.cat/resource/69zu-w48s.json?$limit=23000');

        
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status} - ${response.statusText}`);
        }
        
        const data = await response.json();
        
        if (!data || data.length === 0) {
            throw new Error('No s\'han trobat dades a l\'API');
        }
        
        allData = processData(data);
        filteredData = [...allData];
        
        populateFilters();
        updateChart();
        hideLoading();
        
    } catch (error) {
        console.error('Error loading data:', error);
        showError(error.message);
    }
}

// Process raw data from API
function processData(rawData) {
    return rawData.map(item => ({
        year: parseInt(item.any),
        municipalityCode: item.codi_municipi,
        municipality: item.municipi,
        comarca: item.comarca,
        population: parseInt(item.poblaci) || 0,
        selectiveCollection: parseFloat(item.r_s_r_m_total) || 0,
        nonSelectivePercentage: parseFloat(item.f_r_r_m) || 0,
        totalWasteGeneration: parseFloat(item.generaci_residus_municipal) || 0,
        kgPerHabitantYear: parseFloat(item.kg_hab_any) || 0,
        organicMatter: parseFloat(item.mat_ria_org_nica) || 0,
        paperCardboard: parseFloat(item.paper_i_cartr) || 0,
        glass: parseFloat(item.vidre) || 0,
        lightPackaging: parseFloat(item.envasos_lleugers) || 0,
        bulkyWaste: parseFloat(item.residus_voluminosos_fusta) || 0,
        textile: parseFloat(item.t_xtil) || 0,
        oil: parseFloat(item.olis_vegetals) || 0
    })).filter(item => 
        item.year && item.municipality && !isNaN(item.selectiveCollection)
    );
}

// Populate filter dropdowns
function populateFilters() {
    const yearFilter = document.getElementById('year-filter');
    
    // Get unique years and sort them
    const years = [...new Set(allData.map(item => item.year))].sort((a, b) => b - a);
    years.forEach(year => {
        const option = document.createElement('option');
        option.value = year;
        option.textContent = year;
        yearFilter.appendChild(option);
    });
}

// Apply filters
function applyFilters() {
    const yearFilter = document.getElementById('year-filter').value;
    
    filteredData = allData.filter(item => {
        const yearMatch = yearFilter === 'all' || item.year == yearFilter;
        return yearMatch;
    });
    
    updateChart();
}

// Update chart
function updateChart() {
    const ctx = document.getElementById('regional-chart').getContext('2d');
    
    if (chart) {
        chart.destroy();
    }
    
    // Get selected year from filter or use most recent year
    const yearFilter = document.getElementById('year-filter').value;
    let targetYear;
    
    if (yearFilter === 'all') {
        // Use most recent year available
        const years = [...new Set(allData.map(item => item.year))].sort((a, b) => b - a);
        targetYear = years[0];
    } else {
        targetYear = parseInt(yearFilter);
    }
    
    // Get data for target year from all data (ignore municipality filter)
    const yearData = allData.filter(item => item.year === targetYear);
    
    if (yearData.length === 0) {
        ctx.font = '16px Inter';
        ctx.fillStyle = COLORS.neutral;
        ctx.textAlign = 'center';
        ctx.fillText(`No hi ha dades per al ${targetYear}`, ctx.canvas.width / 2, ctx.canvas.height / 2);
        return;
    }
    
    // Update chart title
    document.getElementById('chart-title').textContent = `🏞️ Comparació per Comarques (${targetYear})`;
    
    // Group by comarca and calculate averages
    const comarcaData = {};
    yearData.forEach(item => {
        if (!comarcaData[item.comarca]) {
            comarcaData[item.comarca] = [];
        }
        comarcaData[item.comarca].push(item.selectiveCollection);
    });
    
    const comarcaAverages = Object.keys(comarcaData).map(comarca => ({
        comarca,
        average: comarcaData[comarca].reduce((sum, val) => sum + val, 0) / comarcaData[comarca].length
    })).sort((a, b) => a.average - b.average).slice(0, 20); // Show bottom 20
    
    chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: comarcaAverages.map(item => item.comarca),
            datasets: [{
                label: 'Recollida Selectiva Mitjana (%)',
                data: comarcaAverages.map(item => item.average),
                backgroundColor: comarcaAverages.map(item => 
                    item.average < 40 ? COLORS.critical :
                    item.average < 50 ? COLORS.warning :
                    COLORS.secondary
                ),
                borderColor: COLORS.neutral,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Percentatge (%)'
                    }
                },
                y: {
                    ticks: {
                        font: {
                            size: 10
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
}

// Utility functions
function showLoading() {
    document.getElementById('loading').classList.remove('hidden');
    document.getElementById('main-content').classList.add('hidden');
    document.getElementById('error').classList.add('hidden');
}

function hideLoading() {
    document.getElementById('loading').classList.add('hidden');
    document.getElementById('main-content').classList.remove('hidden');
}

function showError(message) {
    document.getElementById('loading').classList.add('hidden');
    document.getElementById('main-content').classList.add('hidden');
    document.getElementById('error').classList.remove('hidden');
    document.getElementById('error-message').textContent = message;
}