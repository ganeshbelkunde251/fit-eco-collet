<?php
// user/ai-assistant.php - Gemini AI Waste Assistant
$pageTitle = "AI Waste Assistant — EcoCollect";
$extraJs = 'ai.js';
require_once __DIR__ . '/../config/auth.php';

require_login();
$user = current_user();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-layout">
    <div class="sidebar-wrapper">
        <?php require_once __DIR__ . '/../includes/user-sidebar.php'; ?>
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <h3 class="fw-bold text-dark mb-0">AI Waste Assistant</h3>
                    <span class="badge bg-success-subtle text-success px-3 py-1 rounded-pill fw-semibold">Powered by Google Gemini</span>
                </div>
                <p class="text-muted small mb-0">Describe your waste items to receive intelligent categorization and safety guidelines.</p>
            </div>
        </div>

        <?php require_once __DIR__ . '/../includes/alerts.php'; ?>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                    <h5 class="fw-bold text-dark mb-3"><i class="bi bi-chat-left-text text-success me-2"></i> Describe Waste Items</h5>
                    
                    <form id="ai-classify-form">
                        <div class="mb-3">
                            <label for="waste-description-input" class="form-label fw-semibold small">What items do you want to dispose of?</label>
                            <textarea class="form-control bg-light" id="waste-description-input" rows="4" placeholder="e.g. Old laptop, broken smartphone, lithium battery, and 2 chargers" required></textarea>
                            <small class="text-muted fs-8 mt-1 d-block">Be as descriptive as possible for accurate classification.</small>
                        </div>

                        <button type="submit" id="analyze-btn" class="btn btn-success w-100 py-3 rounded-3 fw-bold shadow-sm">
                            <i class="bi bi-magic me-2"></i> Analyze Waste with Gemini AI
                        </button>
                    </form>

                    <div class="mt-4 p-3 bg-light rounded-3 border">
                        <h6 class="fw-bold text-dark mb-2"><i class="bi bi-lightbulb text-warning me-1"></i> Try Sample Prompts:</h6>
                        <ul class="mb-0 ps-3 small text-muted">
                            <li class="mb-1"><code>Old Samsung Galaxy phone, laptop charger, battery</code></li>
                            <li class="mb-1"><code>Empty paint cans, expired chemical cleaners, spray bottles</code></li>
                            <li><code>Clean pizza boxes, magazines, office paperwork</code></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <!-- Fallback / Error Alert Banner -->
                <div id="ai-error-alert" class="alert alert-warning border-warning d-none rounded-4 p-4 shadow-sm" role="alert">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-exclamation-triangle-fill fs-3 text-warning me-2"></i>
                        <h5 class="fw-bold mb-0 text-dark">AI Assistant Unavailable</h5>
                    </div>
                    <p id="ai-error-message" class="small mb-3">AI assistant is temporarily unavailable. You can still manually select a waste category and submit your request.</p>
                    <a href="create-request.php" class="btn btn-outline-dark btn-sm rounded-pill fw-bold">
                        Continue to Manual Request Form
                    </a>
                </div>

                <!-- Structured Gemini Output Card -->
                <div id="ai-result-card" class="card border-0 shadow-sm rounded-4 p-4 bg-white d-none">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <h5 class="fw-bold text-dark mb-0"><i class="bi bi-robot text-success me-2"></i> AI Classification Analysis</h5>
                        <span class="badge bg-success text-white px-3 py-1 rounded-pill fw-semibold">Gemini AI</span>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted fw-semibold text-uppercase fs-8">Suggested Category</small>
                        <h3 id="res-category" class="fw-bold text-success mb-0 mt-1">--</h3>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted fw-semibold text-uppercase fs-8">Reasoning</small>
                        <p id="res-reason" class="small text-dark mb-0 mt-1">--</p>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted fw-semibold text-uppercase fs-8">Suggested Action</small>
                        <p id="res-action" class="small text-dark mb-0 mt-1">--</p>
                    </div>

                    <div class="mb-4">
                        <small class="text-muted fw-semibold text-uppercase fs-8">Safety Note</small>
                        <div class="p-3 bg-warning-subtle text-dark rounded-3 mt-1 border border-warning">
                            <i class="bi bi-shield-exclamation me-1 text-warning"></i>
                            <span id="res-safety" class="small fw-medium">--</span>
                        </div>
                    </div>

                    <a id="quick-request-btn" href="create-request.php" class="btn btn-success w-100 py-3 rounded-3 fw-bold shadow-sm">
                        <i class="bi bi-plus-circle-fill me-1"></i> Create Request with this Category
                    </a>
                </div>

                <!-- Default Placeholder State -->
                <div id="ai-placeholder-card" class="card border-0 shadow-sm rounded-4 p-5 bg-white text-center text-muted">
                    <i class="bi bi-cpu fs-1 d-block mb-3 text-success"></i>
                    <h5 class="fw-bold text-dark">Ready for Analysis</h5>
                    <p class="small mb-0">Enter a description of your waste items on the left and click "Analyze Waste with Gemini AI" to get instant classification.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
