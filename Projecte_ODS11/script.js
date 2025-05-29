// Variables globals
let currentSlide1 = 0;
let currentSlide2 = 0;
const totalSlides = 4;

// Funcions per canviar entre vista graella i carrussel
function setupViewToggle(sectionId) {
    const gridBtn = document.getElementById(`gridView${sectionId ? sectionId : ''}`);
    const carouselBtn = document.getElementById(`carouselView${sectionId ? sectionId : ''}`);
    const grid = document.getElementById(`chartsGrid${sectionId ? sectionId : ''}`);
    const carousel = document.getElementById(`chartsCarousel${sectionId ? sectionId : ''}`);

    gridBtn.addEventListener('click', () => {
        gridBtn.classList.add('active');
        carouselBtn.classList.remove('active');
        grid.style.display = 'grid';
        carousel.style.display = 'none';
    });

    carouselBtn.addEventListener('click', () => {
        carouselBtn.classList.add('active');
        gridBtn.classList.remove('active');
        grid.style.display = 'none';
        carousel.style.display = 'block';
    });
}

// Funcions del carrussel
function setupCarousel(sectionId, currentSlideVar) {
    const prevBtn = document.getElementById(`prevBtn${sectionId ? sectionId : ''}`);
    const nextBtn = document.getElementById(`nextBtn${sectionId ? sectionId : ''}`);
    const slides = document.getElementById(`carouselSlides${sectionId ? sectionId : ''}`);

    prevBtn.addEventListener('click', () => {
        if (sectionId === '2') {
            currentSlide2 = (currentSlide2 - 1 + totalSlides) % totalSlides;
            updateCarousel(slides, currentSlide2);
        } else {
            currentSlide1 = (currentSlide1 - 1 + totalSlides) % totalSlides;
            updateCarousel(slides, currentSlide1);
        }
    });

    nextBtn.addEventListener('click', () => {
        if (sectionId === '2') {
            currentSlide2 = (currentSlide2 + 1) % totalSlides;
            updateCarousel(slides, currentSlide2);
        } else {
            currentSlide1 = (currentSlide1 + 1) % totalSlides;
            updateCarousel(slides, currentSlide1);
        }
    });
}

function updateCarousel(slidesElement, currentSlide) {
    const translateX = -currentSlide * 100;
    slidesElement.style.transform = `translateX(${translateX}%)`;
}

// Funcions del modal
function openModal(chartUrl) {
    const modal = document.getElementById('chartModal');
    const iframe = document.getElementById('modalIframe');
    iframe.src = chartUrl;
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    const modal = document.getElementById('chartModal');
    const iframe = document.getElementById('modalIframe');
    modal.style.display = 'none';
    iframe.src = '';
    document.body.style.overflow = 'auto';
}

// Tancar modal fent clic fora del contingut
window.addEventListener('click', (e) => {
    const modal = document.getElementById('chartModal');
    if (e.target === modal) {
        closeModal();
    }
});

// Tancar modal amb ESC
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeModal();
    }
});

// Smooth scrolling per els enllaços de navegació
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Inicialització quan es carrega la pàgina
document.addEventListener('DOMContentLoaded', () => {
    // Configurar vista toggle per ambdues seccions
    setupViewToggle('');
    setupViewToggle('2');

    // Configurar carrussel per ambdues seccions
    setupCarousel('', 1);
    setupCarousel('2', 2);

    // Animacions a l'scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Observar elements per animacions
    document.querySelectorAll('.solution-card, .team-member, .conclusion-item').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'all 0.6s ease';
        observer.observe(el);
    });
});

// Actualització automàtica de dades (opcional)
async function fetchWasteData() {
    try {
        const response = await fetch('https://analisi.transparenciacatalunya.cat/resource/69zu-w48s.json');
        const data = await response.json();

        // Aquí pots processar les dades i actualitzar els gràfics
        console.log('Dades obtingudes:', data);

        // Exemple d'actualització de estadístiques
        // updateStatistics(data);

    } catch (error) {
        console.error('Error obtenint les dades:', error);
    }
}

// Cridar la funció per obtenir dades (opcional)
// fetchWasteData();

// Càlcul predicció recollida selectova


// Global variables
let allData = [];
let filteredData = [];
let predictions = {};

// Initialize the application
document.addEventListener('DOMContentLoaded', function () {
    loadData();
});

// Load data from API
async function loadData() {
    try {
        showLoading();

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

        // Calculate predictions based on historical data
        calculatePredictions();

        updatePredictions();
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

// Calculate predictions using linear regression
function calculatePredictions() {
    // Get yearly averages for all Catalunya
    const years = [...new Set(allData.map(item => item.year))].sort();
    const yearlyAverages = years.map(year => {
        const yearData = allData.filter(item => item.year === year);
        const avg = yearData.reduce((sum, item) => sum + item.selectiveCollection, 0) / yearData.length;
        return { year, percentage: avg };
    });

    if (yearlyAverages.length < 3) {
        // Not enough data for reliable prediction
        predictions = { 2025: null, 2030: null, 2035: null };
        return;
    }

    // Linear regression calculation
    const n = yearlyAverages.length;
    const sumX = yearlyAverages.reduce((sum, item) => sum + item.year, 0);
    const sumY = yearlyAverages.reduce((sum, item) => sum + item.percentage, 0);
    const sumXY = yearlyAverages.reduce((sum, item) => sum + (item.year * item.percentage), 0);
    const sumX2 = yearlyAverages.reduce((sum, item) => sum + (item.year * item.year), 0);

    // Calculate slope (m) and intercept (b) for y = mx + b
    const slope = (n * sumXY - sumX * sumY) / (n * sumX2 - sumX * sumX);
    const intercept = (sumY - slope * sumX) / n;

    // Calculate R² (coefficient of determination) for reliability
    const meanY = sumY / n;
    const totalSumSquares = yearlyAverages.reduce((sum, item) => sum + Math.pow(item.percentage - meanY, 2), 0);
    const residualSumSquares = yearlyAverages.reduce((sum, item) => {
        const predicted = slope * item.year + intercept;
        return sum + Math.pow(item.percentage - predicted, 2);
    }, 0);
    const rSquared = 1 - (residualSumSquares / totalSumSquares);

    // Only use predictions if R² > 0.5 (reasonable correlation)
    if (rSquared > 0.5) {
        predictions = {
            2025: Math.max(0, Math.min(100, slope * 2025 + intercept)),
            2030: Math.max(0, Math.min(100, slope * 2030 + intercept)),
            2035: Math.max(0, Math.min(100, slope * 2035 + intercept)),
            reliability: rSquared,
            historicalData: yearlyAverages
        };
    } else {
        // Use average growth rate as fallback
        const recentYears = yearlyAverages.slice(-5); // Last 5 years
        const growthRates = [];

        for (let i = 1; i < recentYears.length; i++) {
            const rate = recentYears[i].percentage - recentYears[i - 1].percentage;
            growthRates.push(rate);
        }

        const avgGrowthRate = growthRates.reduce((sum, rate) => sum + rate, 0) / growthRates.length;
        const lastYear = recentYears[recentYears.length - 1];

        predictions = {
            2025: Math.max(0, Math.min(100, lastYear.percentage + avgGrowthRate * (2025 - lastYear.year))),
            2030: Math.max(0, Math.min(100, lastYear.percentage + avgGrowthRate * (2030 - lastYear.year))),
            2035: Math.max(0, Math.min(100, lastYear.percentage + avgGrowthRate * (2035 - lastYear.year))),
            reliability: 'moderate',
            historicalData: yearlyAverages
        };
    }
}

// Update predictions display
function updatePredictions() {
    const predictionYears = [2025, 2030, 2035];

    predictionYears.forEach(year => {
        const valueElement = document.getElementById(`prediction-${year}`);
        const confidenceElement = document.getElementById(`confidence-${year}`);

        if (predictions[year] !== null && predictions[year] !== undefined) {
            valueElement.textContent = `${predictions[year].toFixed(1)}%`;

            // Show confidence/reliability information
            if (typeof predictions.reliability === 'number') {
                const reliabilityPercent = (predictions.reliability * 100).toFixed(0);
                confidenceElement.textContent = `Fiabilitat: ${reliabilityPercent}% (R²)`;

                // Color based on reliability
                if (predictions.reliability > 0.8) {
                    confidenceElement.style.backgroundColor = '#e8f5e8';
                    confidenceElement.style.color = '#2d7d32';
                } else if (predictions.reliability > 0.6) {
                    confidenceElement.style.backgroundColor = '#fff3e0';
                    confidenceElement.style.color = '#ff9800';
                } else {
                    confidenceElement.style.backgroundColor = '#ffebee';
                    confidenceElement.style.color = '#f44336';
                }
            } else {
                confidenceElement.textContent = 'Estimació basada en tendència recent';
                confidenceElement.style.backgroundColor = '#f5f5f5';
                confidenceElement.style.color = '#6c757d';
            }
        } else {
            valueElement.textContent = '--';
            confidenceElement.textContent = 'Dades insuficients per predicció';
            confidenceElement.style.backgroundColor = '#f5f5f5';
            confidenceElement.style.color = '#6c757d';
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