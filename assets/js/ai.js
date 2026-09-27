/**
 * AI Waste Assistant Handler
 * EcoCollect Management Platform
 */

document.addEventListener('DOMContentLoaded', () => {
    const aiForm = document.getElementById('ai-classify-form');
    const descInput = document.getElementById('waste-description-input');
    const analyzeBtn = document.getElementById('analyze-btn');
    const resultCard = document.getElementById('ai-result-card');
    const errorAlert = document.getElementById('ai-error-alert');

    if (!aiForm) return;

    aiForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const description = descInput.value.trim();
        if (!description) {
            alert('Please enter a description of the waste items.');
            return;
        }

        // UI Loading State
        analyzeBtn.disabled = true;
        const originalBtnHTML = analyzeBtn.innerHTML;
        analyzeBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Analyzing waste with AI...`;

        resultCard.classList.add('d-none');
        errorAlert.classList.add('d-none');

        try {
            const response = await fetch('../api/ai-classify.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ description })
            });

            const data = await response.json();

            if (data.status === 'success' && data.result) {
                const res = data.result;

                document.getElementById('res-category').textContent = res.category || 'Other';
                document.getElementById('res-reason').textContent = res.reason || 'No specific reasoning returned.';
                document.getElementById('res-action').textContent = res.action || 'Schedule a pickup request.';
                document.getElementById('res-safety').textContent = res.safety_note || 'Handle with standard household care.';

                // Setup quick request redirect button
                const quickBtn = document.getElementById('quick-request-btn');
                if (quickBtn) {
                    quickBtn.href = `create-request.php?category=${encodeURIComponent(res.category)}&desc=${encodeURIComponent(description)}`;
                }

                resultCard.classList.remove('d-none');
                resultCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
            } else {
                throw new Exception(data.message || 'AI service returned an invalid response.');
            }
        } catch (err) {
            console.warn('AI Assistant Warning/Error:', err);
            errorAlert.classList.remove('d-none');
            document.getElementById('ai-error-message').textContent = err.message || 'AI assistant is temporarily unavailable. You can still manually select a waste category and submit your request.';
        } finally {
            analyzeBtn.disabled = false;
            analyzeBtn.innerHTML = originalBtnHTML;
        }
    });
});
