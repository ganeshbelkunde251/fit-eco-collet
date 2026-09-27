/**
 * AI Camera Waste Scanner Handler
 * EcoCollect Management Platform
 */

document.addEventListener('DOMContentLoaded', () => {
    const startCameraBtn = document.getElementById('btn-start-camera');
    const stopCameraBtn = document.getElementById('btn-stop-camera');
    const captureScanBtn = document.getElementById('btn-capture-scan');
    const scanAgainBtn = document.getElementById('btn-scan-again');
    
    const cameraSection = document.getElementById('scanner-camera-section');
    const videoElem = document.getElementById('scanner-video');
    const canvasElem = document.getElementById('scanner-canvas');
    const fileInput = document.getElementById('scanner-file-input');
    
    const loadingState = document.getElementById('scanner-loading-state');
    const resultCard = document.getElementById('scanner-result-card');
    const errorAlert = document.getElementById('scanner-error-alert');
    const placeholder = document.getElementById('scanner-placeholder');

    let mediaStream = null;

    if (!startCameraBtn) return;

    // Start Live Camera
    startCameraBtn.addEventListener('click', async () => {
        startCameraBtn.classList.add('d-none');
        cameraSection.classList.remove('d-none');
        resultCard.classList.add('d-none');
        errorAlert.classList.add('d-none');
        if (placeholder) placeholder.classList.add('d-none');

        try {
            // Attempt rear environment camera first, fallback to user camera
            let constraints = { video: { facingMode: { ideal: 'environment' }, width: { ideal: 1280 }, height: { ideal: 720 } } };
            try {
                mediaStream = await navigator.mediaDevices.getUserMedia(constraints);
            } catch (err) {
                mediaStream = await navigator.mediaDevices.getUserMedia({ video: true });
            }

            videoElem.srcObject = mediaStream;
            await videoElem.play();
        } catch (err) {
            console.warn('Camera Access Error:', err);
            cameraSection.classList.add('d-none');
            startCameraBtn.classList.remove('d-none');
            showScannerError('Camera access unavailable or permission denied. Please select an image file below to scan.');
        }
    });

    // Stop/Close Camera
    stopCameraBtn?.addEventListener('click', () => {
        stopMediaTracks();
        cameraSection.classList.add('d-none');
        startCameraBtn.classList.remove('d-none');
        if (placeholder) placeholder.classList.remove('d-none');
    });

    // Capture & Scan from Camera
    captureScanBtn?.addEventListener('click', () => {
        if (!videoElem.srcObject) return;

        // Draw video frame onto hidden canvas
        const context = canvasElem.getContext('2d');
        canvasElem.width = videoElem.videoWidth || 640;
        canvasElem.height = videoElem.videoHeight || 480;
        context.drawImage(videoElem, 0, 0, canvasElem.width, canvasElem.height);

        const base64Image = canvasElem.toDataURL('image/jpeg', 0.85);

        // Stop camera stream after capturing
        stopMediaTracks();
        cameraSection.classList.add('d-none');
        startCameraBtn.classList.remove('d-none');

        // Send to backend API
        sendImageToAI(base64Image);
    });

    // Handle File Input Upload Fallback
    fileInput?.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;

        if (!['image/jpeg', 'image/jpg', 'image/png'].includes(file.type)) {
            alert('Please select a valid JPG, JPEG, or PNG image file.');
            return;
        }

        const reader = new FileReader();
        reader.onload = function (e) {
            stopMediaTracks();
            cameraSection.classList.add('d-none');
            startCameraBtn.classList.remove('d-none');
            sendImageToAI(e.target.result);
        };
        reader.readAsDataURL(file);
    });

    // Reset Scanner / Scan Again
    scanAgainBtn?.addEventListener('click', () => {
        resultCard.classList.add('d-none');
        errorAlert.classList.add('d-none');
        if (fileInput) fileInput.value = '';
        startCameraBtn.click();
    });

    // Helper: Send Image to Backend API
    async function sendImageToAI(base64Image) {
        loadingState.classList.remove('d-none');
        resultCard.classList.add('d-none');
        errorAlert.classList.add('d-none');
        if (placeholder) placeholder.classList.add('d-none');

        try {
            const response = await fetch('../api/ai-image-classify.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ image: base64Image })
            });

            const data = await response.json();

            if ((data.status === 'success' || data.status === 'fallback') && data.result) {
                const res = data.result;

                document.getElementById('scan-res-object').textContent = res.object_name || 'Scanned Waste Item';
                document.getElementById('scan-res-category').textContent = res.category || 'Other';
                document.getElementById('scan-res-type').textContent = res.waste_type || 'General Waste';
                
                const recElem = document.getElementById('scan-res-recyclable');
                if (recElem) {
                    if (res.recyclable === 'Yes') {
                        recElem.className = 'badge bg-success text-white px-3 py-1 rounded-pill';
                        recElem.innerHTML = '<i class="bi bi-check-circle me-1"></i> Yes';
                    } else {
                        recElem.className = 'badge bg-secondary text-white px-3 py-1 rounded-pill';
                        recElem.innerHTML = '<i class="bi bi-x-circle me-1"></i> No';
                    }
                }

                document.getElementById('scan-res-disposal').textContent = res.disposal_method || 'Follow standard waste management guidelines.';
                document.getElementById('scan-res-safety').textContent = res.safety_warning || 'Handle items with appropriate care.';
                document.getElementById('scan-res-confidence').textContent = res.confidence_notes || 'Scanned using Gemini Vision AI.';

                // Setup quick request redirect button
                const quickBtn = document.getElementById('scan-quick-request-btn');
                if (quickBtn) {
                    quickBtn.href = `create-request.php?category=${encodeURIComponent(res.category)}&desc=${encodeURIComponent(res.object_name + ' - ' + res.waste_type)}`;
                }

                resultCard.classList.remove('d-none');
                resultCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
            } else {
                throw new Error(data.message || 'AI scanner API returned an invalid response.');
            }
        } catch (err) {
            console.error('AI Scanner Error:', err);
            showScannerError(err.message || 'AI Vision assistant is temporarily unavailable. You can still manually select a waste category.');
        } finally {
            loadingState.classList.add('d-none');
        }
    }

    function showScannerError(msg) {
        errorAlert.classList.remove('d-none');
        document.getElementById('scanner-error-message').textContent = msg;
        if (placeholder) placeholder.classList.remove('d-none');
    }

    function stopMediaTracks() {
        if (mediaStream) {
            mediaStream.getTracks().forEach(track => track.stop());
            mediaStream = null;
        }
        if (videoElem) {
            videoElem.srcObject = null;
        }
    }
});
