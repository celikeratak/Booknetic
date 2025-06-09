(function ($) {
    "use strict";
    
    // Define booknetic.__ function if it doesn't exist
    if (typeof booknetic === 'undefined') {
        window.booknetic = {};
    }
    
    if (typeof booknetic.__ !== 'function') {
        booknetic.__ = function(text) {
            // Try to get translation from helpCenterI18n first
            if (typeof helpCenterI18n !== 'undefined' && helpCenterI18n[text]) {
                return helpCenterI18n[text];
            }
            return text; // Simple fallback that returns the original text
        };
    }
    
    // Get AJAX URL and nonce from helpCenterAjax global object
    const ajaxUrl = typeof helpCenterAjax !== 'undefined' && helpCenterAjax.ajaxUrl ? helpCenterAjax.ajaxUrl : (typeof ajaxurl !== 'undefined' ? ajaxurl : '');
    const nonce = typeof helpCenterAjax !== 'undefined' && helpCenterAjax.nonce ? helpCenterAjax.nonce : '';

    $(document).ready(function() {
        // View switcher
        const viewSwitches = document.querySelectorAll('.view-switch');
        const categoriesList = document.querySelector('.categories-list');
        
        // Always use grid view as default
        const defaultView = 'grid';
        setView(defaultView);
        localStorage.setItem('helpCenterView', defaultView);
        
        viewSwitches.forEach(button => {
            button.addEventListener('click', () => {
                const view = button.dataset.view;
                setView(view);
                localStorage.setItem('helpCenterView', view);
            });
        });
        
        function setView(view) {
            // Update buttons
            viewSwitches.forEach(btn => {
                btn.classList.toggle('active', btn.dataset.view === view);
            });
            // Update list class
            if (categoriesList) {
                categoriesList.classList.remove('grid-view', 'list-view');
                categoriesList.classList.add(`${view}-view`);
            }
        }

        // Enhanced Image Viewer Functionality
        const images = document.querySelectorAll('.topic-content img');

        if (images.length > 0) {
            // Make all images in content have a zoom cursor and add a small zoom icon
            images.forEach(img => {
                img.classList.add('zoomable-image');
                
                // Create zoom indicator
                const zoomIndicator = document.createElement('div');
                zoomIndicator.classList.add('zoom-indicator');
                zoomIndicator.innerHTML = '<i class="fas fa-search-plus"></i>';
                
                // Insert the zoom indicator after the image
                img.parentNode.insertBefore(zoomIndicator, img.nextSibling);
                
                // Position the zoom indicator relative to the image
                setTimeout(() => {
                    const imgRect = img.getBoundingClientRect();
                    zoomIndicator.style.top = `${img.offsetTop + 10}px`;
                    zoomIndicator.style.left = `${img.offsetLeft + 10}px`;
                }, 100);
            });
            
            // Create the overlay
            const overlay = document.createElement('div');
            overlay.classList.add('image-overlay');
            document.body.appendChild(overlay);

            // Create the image container for better handling of transformations
            const imageContainer = document.createElement('div');
            imageContainer.classList.add('image-container');
            overlay.appendChild(imageContainer);
            
            // Create the zoomed image
            const zoomedImg = document.createElement('img');
            zoomedImg.classList.add('zoomed-image');
            imageContainer.appendChild(zoomedImg);

            // Create zoom controls container
            const zoomControls = document.createElement('div');
            zoomControls.classList.add('zoom-controls');
            overlay.appendChild(zoomControls);
            
            // Zoom in button
            const zoomInBtn = document.createElement('button');
            zoomInBtn.classList.add('zoom-button', 'zoom-in');
            zoomInBtn.innerHTML = '<i class="fas fa-search-plus"></i>';
            zoomInBtn.setAttribute('title', booknetic.__('Zoom In'));
            zoomControls.appendChild(zoomInBtn);
            
            // Zoom out button
            const zoomOutBtn = document.createElement('button');
            zoomOutBtn.classList.add('zoom-button', 'zoom-out');
            zoomOutBtn.innerHTML = '<i class="fas fa-search-minus"></i>';
            zoomOutBtn.setAttribute('title', booknetic.__('Zoom Out'));
            zoomControls.appendChild(zoomOutBtn);
            
            // Reset zoom button
            const resetZoomBtn = document.createElement('button');
            resetZoomBtn.classList.add('zoom-button', 'reset-zoom');
            resetZoomBtn.innerHTML = '<i class="fas fa-compress-arrows-alt"></i>';
            resetZoomBtn.setAttribute('title', booknetic.__('Reset Zoom'));
            zoomControls.appendChild(resetZoomBtn);
            
            // Zoom level indicator
            const zoomLevelIndicator = document.createElement('div');
            zoomLevelIndicator.classList.add('zoom-level-indicator');
            zoomControls.appendChild(zoomLevelIndicator);

            // Navigation Buttons
            const prevBtn = document.createElement('button');
            prevBtn.classList.add('nav-button', 'prev');
            prevBtn.innerHTML = '<i class="fas fa-chevron-left"></i>';
            prevBtn.setAttribute('title', booknetic.__('Previous Image'));
            overlay.appendChild(prevBtn);

            const nextBtn = document.createElement('button');
            nextBtn.classList.add('nav-button', 'next');
            nextBtn.innerHTML = '<i class="fas fa-chevron-right"></i>';
            nextBtn.setAttribute('title', booknetic.__('Next Image'));
            overlay.appendChild(nextBtn);

            // Image counter
            const imageCounter = document.createElement('div');
            imageCounter.classList.add('image-counter');
            overlay.appendChild(imageCounter);

            // Close Button
            const closeBtn = document.createElement('button');
            closeBtn.classList.add('close-button');
            closeBtn.innerHTML = '<i class="fas fa-times"></i>';
            closeBtn.setAttribute('title', booknetic.__('Close'));
            overlay.appendChild(closeBtn);

            // State variables
            let currentIndex = 0;
            let scale = 1; 
            let maxScale = 5;
            let minScale = 0.5;
            let scaleStep = 0.2; // Increased for smoother zooming
            let isDragging = false;
            let startX, startY, translateX = 0, translateY = 0;
            let initialPinchDistance = 0;
            let initialScale = 1;
            let lastTap = 0; // For double tap detection on mobile
            let lastPinchScale = 1; // For smooth pinch zooming
            let doubleTapZoom = 2.5; // Zoom level for double tap

            // Show image at specified index
            function showImage(index) {
                if (index < 0) index = images.length - 1;
                if (index >= images.length) index = 0;
                
                currentIndex = index;
                
                // Preload the image to get natural dimensions
                const tempImg = new Image();
                tempImg.onload = function() {
                    // Reset zoom and position
                    resetZoom();
                    
                    // Set the source after preloading
                    zoomedImg.src = this.src;
                    
                    // Update image counter
                    updateImageCounter();
                    
                    // Show overlay with animation
                    overlay.style.display = 'flex';
                    setTimeout(() => overlay.classList.add('active'), 10);
                };
                
                tempImg.src = images[currentIndex].src;
                
                // Set alt text if available
                if (images[currentIndex].alt) {
                    zoomedImg.alt = images[currentIndex].alt;
                }
            }

            // Update image counter display
            function updateImageCounter() {
                imageCounter.textContent = `${currentIndex + 1} / ${images.length}`;
            }

            // Update zoom level indicator
            function updateZoomLevel() {
                const percentage = Math.round(scale * 100);
                zoomLevelIndicator.textContent = `${percentage}%`;
                
                // Enable/disable zoom buttons based on limits
                zoomOutBtn.disabled = scale <= minScale;
                zoomInBtn.disabled = scale >= maxScale;
            }

            // Reset zoom and position
            function resetZoom() {
                scale = 1;
                translateX = 0;
                translateY = 0;
                
                // Add a subtle animation for smooth reset
                zoomedImg.style.transition = 'transform 0.3s ease';
                applyTransform();
                updateZoomLevel();
                
                // Remove transition after animation completes
                setTimeout(() => {
                    zoomedImg.style.transition = '';
                }, 300);
            }

            // Apply transformation to the image
            function applyTransform() {
                // Use translate3d for hardware acceleration
                zoomedImg.style.transform = `scale(${scale}) translate3d(${translateX / scale}px, ${translateY / scale}px, 0)`;
                updateZoomLevel();
                
                // Update cursor based on zoom level
                imageContainer.style.cursor = scale > 1 ? 'grab' : 'zoom-out';
                if (isDragging) {
                    imageContainer.style.cursor = 'grabbing';
                }
            }

            // Close the overlay with animation
            function closeOverlay() {
                overlay.classList.remove('active');
                setTimeout(() => {
                    overlay.style.display = 'none';
                    resetZoom();
                }, 300);
            }

            // Zoom in function
            function zoomIn(amount = scaleStep, centerX = null, centerY = null) {
                if (scale < maxScale) {
                    const prevScale = scale;
                    scale = Math.min(maxScale, scale + amount);
                    
                    // If center coordinates are provided, zoom towards that point
                    if (centerX !== null && centerY !== null) {
                        // Adjust translation to zoom towards the point
                        const imageRect = zoomedImg.getBoundingClientRect();
                        const imageX = (centerX - imageRect.left) - imageRect.width / 2;
                        const imageY = (centerY - imageRect.top) - imageRect.height / 2;
                        
                        // Calculate how much to adjust translation
                        const scaleFactor = scale / prevScale - 1;
                        translateX -= imageX * scaleFactor;
                        translateY -= imageY * scaleFactor;
                    }
                    
                    applyTransform();
                }
            }

            // Zoom out function
            function zoomOut(amount = scaleStep, centerX = null, centerY = null) {
                if (scale > minScale) {
                    const prevScale = scale;
                    scale = Math.max(minScale, scale - amount);
                    
                    // If center coordinates are provided, zoom away from that point
                    if (centerX !== null && centerY !== null) {
                        // Adjust translation to zoom away from the point
                        const imageRect = zoomedImg.getBoundingClientRect();
                        const imageX = (centerX - imageRect.left) - imageRect.width / 2;
                        const imageY = (centerY - imageRect.top) - imageRect.height / 2;
                        
                        // Calculate how much to adjust translation
                        const scaleFactor = 1 - scale / prevScale;
                        translateX += imageX * scaleFactor;
                        translateY += imageY * scaleFactor;
                    }
                    
                    // If we're at minimum scale, reset position
                    if (scale <= 1) {
                        translateX = 0;
                        translateY = 0;
                    }
                    
                    applyTransform();
                }
            }

            // Calculate distance between two touch points
            function getTouchDistance(touches) {
                return Math.hypot(
                    touches[0].clientX - touches[1].clientX,
                    touches[0].clientY - touches[1].clientY
                );
            }

            // Get center point between two touches
            function getTouchCenter(touches) {
                return {
                    x: (touches[0].clientX + touches[1].clientX) / 2,
                    y: (touches[0].clientY + touches[1].clientY) / 2
                };
            }

            // Add click event to all images
            images.forEach((img, index) => {
                img.addEventListener('click', (e) => {
                    e.preventDefault();
                    showImage(index);
                });
            });

            // Navigation button events
            prevBtn.addEventListener('click', () => showImage(currentIndex - 1));
            nextBtn.addEventListener('click', () => showImage(currentIndex + 1));
            
            // Zoom control events
            zoomInBtn.addEventListener('click', () => zoomIn());
            zoomOutBtn.addEventListener('click', () => zoomOut());
            resetZoomBtn.addEventListener('click', resetZoom);
            
            // Close button event
            closeBtn.addEventListener('click', closeOverlay);

            // Close when clicking on the overlay background
            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) closeOverlay();
            });

            // Mouse wheel zoom with improved behavior
            overlay.addEventListener('wheel', (e) => {
                e.preventDefault();
                
                // Get the mouse position relative to the viewport
                const mouseX = e.clientX;
                const mouseY = e.clientY;
                
                // Determine zoom direction and amount based on delta
                // Use a variable zoom step based on current scale for smoother zooming
                const zoomAmount = scaleStep * (scale < 2 ? 1 : scale / 2);
                
                if (e.deltaY < 0) {
                    // Zoom in towards mouse position
                    zoomIn(zoomAmount, mouseX, mouseY);
                } else {
                    // Zoom out from mouse position
                    zoomOut(zoomAmount, mouseX, mouseY);
                }
            }, { passive: false }); // Add passive: false for better performance

            // Double-click to toggle zoom with improved positioning
            zoomedImg.addEventListener('dblclick', (e) => {
                if (scale === 1) {
                    // Get the mouse position
                    const mouseX = e.clientX;
                    const mouseY = e.clientY;
                    
                    // Zoom in to the clicked position with a smooth animation
                    zoomedImg.style.transition = 'transform 0.3s ease';
                    zoomIn(1.5, mouseX, mouseY); // Zoom to 2.5x (1 + 1.5)
                    
                    setTimeout(() => {
                        zoomedImg.style.transition = '';
                    }, 300);
                } else {
                    // Reset to 1x with animation
                    resetZoom();
                }
            });

            // Mouse drag to pan when zoomed in with improved behavior
            zoomedImg.addEventListener('mousedown', (e) => {
                if (scale > 1) {
                    e.preventDefault(); // Prevent text selection during drag
                    isDragging = true;
                    startX = e.clientX - translateX;
                    startY = e.clientY - translateY;
                    imageContainer.style.cursor = 'grabbing';
                    
                    // Disable any transitions for immediate response
                    zoomedImg.style.transition = 'none';
                }
            });

            document.addEventListener('mousemove', (e) => {
                if (isDragging) {
                    e.preventDefault();
                    translateX = e.clientX - startX;
                    translateY = e.clientY - startY;
                    
                    // Limit panning to prevent image from being moved too far off-screen
                    const imageRect = zoomedImg.getBoundingClientRect();
                    const containerRect = imageContainer.getBoundingClientRect();
                    
                    // Calculate boundaries based on image and container sizes
                    const maxTranslateX = (imageRect.width * scale - containerRect.width) / 2;
                    const maxTranslateY = (imageRect.height * scale - containerRect.height) / 2;
                    
                    // Apply boundaries if needed
                    if (maxTranslateX > 0) {
                        translateX = Math.min(Math.max(translateX, -maxTranslateX), maxTranslateX);
                    }
                    
                    if (maxTranslateY > 0) {
                        translateY = Math.min(Math.max(translateY, -maxTranslateY), maxTranslateY);
                    }
                    
                    applyTransform();
                }
            });

            document.addEventListener('mouseup', () => {
                if (isDragging) {
                    isDragging = false;
                    imageContainer.style.cursor = scale > 1 ? 'grab' : 'zoom-out';
                    
                    // If scale is very close to 1, snap to exactly 1
                    if (scale < 1.05 && scale > 0.95) {
                        scale = 1;
                        translateX = 0;
                        translateY = 0;
                        
                        // Add a subtle animation for smooth reset
                        zoomedImg.style.transition = 'transform 0.2s ease';
                        applyTransform();
                        setTimeout(() => {
                            zoomedImg.style.transition = '';
                        }, 200);
                    }
                }
            });

            // Touch events for mobile devices
            zoomedImg.addEventListener('touchstart', (e) => {
                if (e.touches.length === 1) {
                    // Single touch - prepare for panning
                    isDragging = true;
                    startX = e.touches[0].clientX - translateX;
                    startY = e.touches[0].clientY - translateY;
                    
                    // Double tap detection
                    const currentTime = new Date().getTime();
                    const tapLength = currentTime - lastTap;
                    
                    if (tapLength < 300 && tapLength > 0) {
                        // Double tap detected
                        e.preventDefault();
                        if (scale === 1) {
                            // Zoom in to the tap position
                            const touchX = e.touches[0].clientX;
                            const touchY = e.touches[0].clientY;
                            
                            // Zoom in to the tapped point with animation
                            zoomedImg.style.transition = 'transform 0.3s ease';
                            zoomIn(doubleTapZoom - 1, touchX, touchY);
                            
                            setTimeout(() => {
                                zoomedImg.style.transition = '';
                            }, 300);
                        } else {
                            // Reset zoom with animation
                            resetZoom();
                        }
                    }
                    
                    lastTap = currentTime;
                } 
                else if (e.touches.length === 2) {
                    // Two finger pinch - prepare for pinch zoom
                    e.preventDefault();
                    initialPinchDistance = getTouchDistance(e.touches);
                    initialScale = scale;
                    lastPinchScale = scale;
                    
                    // Get the center point of the pinch
                    const center = getTouchCenter(e.touches);
                    startX = center.x;
                    startY = center.y;
                    
                    // Set transition for smoother pinch zoom
                    zoomedImg.style.transition = 'transform 0.05s ease';
                }
            }, { passive: false });

            zoomedImg.addEventListener('touchmove', (e) => {
                e.preventDefault();
                
                if (e.touches.length === 1 && isDragging && scale > 1) {
                    // Single touch - panning (only when zoomed in)
                    translateX = e.touches[0].clientX - startX;
                    translateY = e.touches[0].clientY - startY;
                    
                    // Limit panning to prevent image from being moved too far off-screen
                    const imageRect = zoomedImg.getBoundingClientRect();
                    const containerRect = imageContainer.getBoundingClientRect();
                    
                    // Calculate boundaries based on image and container sizes
                    const maxTranslateX = (imageRect.width * scale - containerRect.width) / 2;
                    const maxTranslateY = (imageRect.height * scale - containerRect.height) / 2;
                    
                    // Apply boundaries
                    if (maxTranslateX > 0) {
                        translateX = Math.min(Math.max(translateX, -maxTranslateX), maxTranslateX);
                    }
                    
                    if (maxTranslateY > 0) {
                        translateY = Math.min(Math.max(translateY, -maxTranslateY), maxTranslateY);
                    }
                    
                    applyTransform();
                } 
                else if (e.touches.length === 2) {
                    // Handle pinch zoom
                    const currentDistance = getTouchDistance(e.touches);
                    if (initialPinchDistance === 0) return; // Prevent division by zero
                    
                    const pinchRatio = currentDistance / initialPinchDistance;
                    const newScale = Math.max(minScale, Math.min(maxScale, initialScale * pinchRatio));
                    
                    // Smooth out the scale changes for more natural feeling
                    const scaleDiff = newScale - lastPinchScale;
                    scale = lastPinchScale + scaleDiff * 0.5; // Apply 50% of the change for smoother effect
                    lastPinchScale = scale;
                    
                    // Get the center point of the pinch
                    const center = getTouchCenter(e.touches);
                    
                    // Calculate how much to adjust translation to keep pinch center fixed
                    const imageRect = zoomedImg.getBoundingClientRect();
                    const pinchCenterX = center.x - imageRect.left;
                    const pinchCenterY = center.y - imageRect.top;
                    
                    // Adjust translation to keep the pinch center point fixed
                    const scaleRatio = scale / initialScale;
                    translateX = center.x - startX * scaleRatio;
                    translateY = center.y - startY * scaleRatio;
                    
                    applyTransform();
                }
            }, { passive: false });

            zoomedImg.addEventListener('touchend', (e) => {
                if (e.touches.length < 2) {
                    initialPinchDistance = 0;
                }
                
                if (e.touches.length === 0) {
                    isDragging = false;
                    
                    // Reset transition after pinch zoom
                    setTimeout(() => {
                        zoomedImg.style.transition = '';
                    }, 50);
                    
                    // If scale is very close to 1, snap to exactly 1
                    if (scale < 1.05 && scale > 0.95) {
                        scale = 1;
                        translateX = 0;
                        translateY = 0;
                        
                        // Add a subtle animation for smooth reset
                        zoomedImg.style.transition = 'transform 0.2s ease';
                        applyTransform();
                        setTimeout(() => {
                            zoomedImg.style.transition = '';
                        }, 200);
                    }
                } else if (e.touches.length === 1) {
                    // If we were pinching and now have one finger, update for panning
                    startX = e.touches[0].clientX - translateX;
                    startY = e.touches[0].clientY - translateY;
                }
            });

            // Keyboard navigation and controls
            document.addEventListener('keydown', (e) => {
                if (!overlay.classList.contains('active')) return;
                
                switch (e.key) {
                    case 'ArrowLeft':
                        showImage(currentIndex - 1);
                        break;
                    case 'ArrowRight':
                        showImage(currentIndex + 1);
                        break;
                    case 'Escape':
                        closeOverlay();
                        break;
                    case '+':
                    case '=':
                        zoomIn();
                        break;
                    case '-':
                    case '_':
                        zoomOut();
                        break;
                    case '0':
                        resetZoom();
                        break;
                }
            });
            
            // Handle window resize
            window.addEventListener('resize', () => {
                if (overlay.classList.contains('active')) {
                    resetZoom();
                }
            });
        }

        // Preserve scroll position after form submission
        if (performance.navigation.type === 2 && sessionStorage.getItem('scrollPosition')) {
            window.scrollTo(0, sessionStorage.getItem('scrollPosition'));
        }
        
        // Store scroll position before unload
        window.addEventListener('beforeunload', function() {
            sessionStorage.setItem('scrollPosition', window.pageYOffset);
        });

        jQuery(document).ready(function ($) {
            // Search functionality
            const searchInput = $("#helpSearchInput");
            const suggestionsContainer = $(".autocomplete-suggestions");
            const searchResultsContainer = $(".search-results-container");
            const loadingIndicator = $(".loading");
            const noResultsContainer = $(".no-results-container");
            const errorMessage = $(".error");
            const closeButton = $(".close-suggestions");
            const minChars = 3;
            const debounceTime = 500;
            const ajaxUrl = typeof helpCenterAjax !== 'undefined' ? helpCenterAjax.ajaxUrl : ajaxurl;
            const nonce = typeof helpCenterAjax !== 'undefined' ? helpCenterAjax.nonce : '';
            
            let searchTimeout;
            
            // Function to highlight search terms in text
            function highlightSearchTerms(text, searchTerm) {
                if (!text || !searchTerm) return text;
                
                // Split search term into words
                const searchWords = searchTerm.split(/\s+/).filter(word => word.length > 2);
                let highlightedText = text;
                
                // First try to highlight the exact search term
                const regex = new RegExp('(' + escapeRegExp(searchTerm) + ')', 'gi');
                highlightedText = highlightedText.replace(regex, '<strong>$1</strong>');
                
                // Then highlight individual words
                searchWords.forEach(word => {
                    const wordRegex = new RegExp('\\b(' + escapeRegExp(word) + ')\\b', 'gi');
                    highlightedText = highlightedText.replace(wordRegex, '<strong>$1</strong>');
                });
                
                return highlightedText;
            }
            
            // Helper function to escape special characters in regex
            function escapeRegExp(string) {
                return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            }
            
            // Helper function to get URL parameters
            function getUrlParam(param) {
                const urlParams = new URLSearchParams(window.location.search);
                return urlParams.get(param);
            }
            
            // Function to fetch and display popular searches
            function fetchPopularSearches() {
                $.ajax({
                    url: ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'help_center_popular_searches',
                        nonce: nonce
                    },
                    beforeSend: function() {
                        loadingIndicator.show();
                        noResultsContainer.hide();
                        errorMessage.hide();
                    },
                    success: function(response) {
                        if (response.success && response.data.terms && response.data.terms.length > 0) {
                            // Create header for suggestions container
                            const headerHtml = `
                                <div class="search-results-header">
                                    <h4><i class="fas fa-search"></i> ${typeof helpCenterI18n !== 'undefined' && helpCenterI18n.search_suggestions ? helpCenterI18n.search_suggestions : booknetic.__('Search Suggestions')}</h4>
                                    <span class="close-suggestions">&times;</span>
                                </div>
                            `;
                            
                            // Create popular searches section with icon
                            const popularSearchesHtml = `
                                <div class="popular-searches">
                                    <h5><i class="fas fa-fire"></i> ${typeof helpCenterI18n !== 'undefined' && helpCenterI18n.popular_searches ? helpCenterI18n.popular_searches : booknetic.__('Popular Searches')}</h5>
                                    <ul class="popular-search-list">
                                        ${response.data.terms.map(item => `
                                            <li>
                                                <a href="${item.url}" class="popular-search-term">
                                                    <i class="fas fa-search-plus"></i>
                                                    <span>${item.term}</span>
                                                </a>
                                            </li>
                                        `).join('')}
                                    </ul>
                                </div>
                            `;
                            
                            // Combine header and popular searches
                            searchResultsContainer.html(headerHtml + popularSearchesHtml);
                            suggestionsContainer.addClass('visible').show();
                            
                            // Reinitialize close button handler
                            $('.close-suggestions').on('click', function() {
                                hideSearchSuggestions();
                            });
                        } else {
                            noResultsContainer.show();
                        }
                    },
                    error: function(xhr, status, error) {
                        errorMessage.text('Error loading popular searches: ' + error).show();
                    },
                    complete: function() {
                        loadingIndicator.hide();
                    }
                });
            }
            
            // Search input handler
            searchInput.on("input", function() {
                const searchTerm = $(this).val().trim();
                
                // Clear any existing timeout
                clearTimeout(searchTimeout);
                
                // Hide suggestions if search term is empty
                if (searchTerm.length === 0) {
                    hideSearchSuggestions();
                    return;
                }
                
                // Show loading indicator and hide other messages
                if (searchTerm.length >= minChars) {
                    showSearchSuggestions();
                    loadingIndicator.show();
                    noResultsContainer.hide();
                    errorMessage.hide();
                    searchResultsContainer.hide();
                    
                    // Add a subtle pulse animation to the search box
                    $(".search-box").addClass("searching");
                    
                    // Set a timeout to prevent too many requests
                    searchTimeout = setTimeout(function() {
                        // Get current page and module from URL
                        const urlParams = new URLSearchParams(window.location.search);
                        const currentPage = urlParams.get('page') || '';
                        const currentModule = urlParams.get('module') || '';
                        
                        $.ajax({
                            url: ajaxUrl,
                            type: 'POST',
                            data: {
                                action: 'help_center_search',
                                term: searchTerm,
                                _wpnonce: nonce,
                                page: currentPage,
                                module: currentModule
                            },
                            success: function(response) {
                                if (response.success && response.data.results && response.data.results.length > 0) {
                                    // Group results by category
                                    const resultsByCategory = {};
                                    
                                    response.data.results.forEach(function(result) {
                                        if (!resultsByCategory[result.category_name]) {
                                            resultsByCategory[result.category_name] = [];
                                        }
                                        resultsByCategory[result.category_name].push(result);
                                    });
                                    
                                    // Build HTML for search results
                                    let resultsHtml = '';
                                    
                                    // Create category groups
                                    for (const category in resultsByCategory) {
                                        resultsHtml += `<div class="category-group">`;
                                        resultsHtml += `<h5><i class="fas fa-folder"></i> ${category}</h5>`;
                                        
                                        resultsByCategory[category].forEach(function(result) {
                                            const highlightedTitle = highlightSearchTerms(result.title, searchTerm);
                                            
                                            resultsHtml += `
                                                <a href="${result.url}" class="search-result-item">
                                                    <div class="result-title"><i class="fas fa-file-alt"></i>${highlightedTitle}</div>
                                                </a>
                                            `;
                                        });
                                        
                                        resultsHtml += `</div>`;
                                    }
                                    

                                    
                                    // Add "View all results" button
                                    const viewAllUrl = `admin.php?page=booknetic-saas&module=help-center&search=${encodeURIComponent(searchTerm)}`;
                                    resultsHtml += `
                                        <div class="search-results-footer">
                                            <a href="${viewAllUrl}" class="view-all-results">
                                                ${typeof helpCenterI18n !== 'undefined' && helpCenterI18n.view_all_results ? helpCenterI18n.view_all_results : booknetic.__('View all results')} <i class="fas fa-arrow-right"></i>
                                            </a>
                                        </div>
                                    `;
                                    
                                    searchResultsContainer.html(resultsHtml).show();
                                    noResultsContainer.hide();
                                } else {
                                    // Show enhanced no results section directly in the search results container
                                    const noResultsHtml = `
                                        <div class="no-results-content">
                                            <div class="no-results-icon animate-pulse">
                                                <svg width="100" height="100" viewBox="0 0 150 150">
                                                    <circle cx="75" cy="75" r="60" fill="var(--primary-color-back)"/>
                                                    <path d="M75 40V75" stroke="var(--primary-color)" stroke-width="5" stroke-linecap="round"/>
                                                    <circle cx="75" cy="95" r="5" fill="var(--primary-color)"/>
                                                </svg>
                                            </div>
                                            <h4 class="no-results-title animate-fade-in">${typeof helpCenterI18n !== 'undefined' && helpCenterI18n.no_results_found ? helpCenterI18n.no_results_found : booknetic.__('No results found')}</h4>
                                            <p class="no-results-message animate-fade-in">
                                                ${typeof helpCenterI18n !== 'undefined' && helpCenterI18n.no_results_for ? helpCenterI18n.no_results_for : booknetic.__('We couldn\'t find any results for')} <span class="search-term">${searchTerm}</span>
                                            </p>
                                            <div class="no-results-suggestions animate-fade-in-up">
                                                <ul>
                                                    <li class="suggestion-item">
                                                        <div class="suggestion-icon"><i class="fas fa-spell-check"></i></div>
                                                        <div class="suggestion-text">${typeof helpCenterI18n !== 'undefined' && helpCenterI18n.check_spelling ? helpCenterI18n.check_spelling : booknetic.__('Check your spelling and try again')}</div>
                                                    </li>
                                                    <li class="suggestion-item">
                                                        <div class="suggestion-icon"><i class="fas fa-search"></i></div>
                                                        <div class="suggestion-text">${typeof helpCenterI18n !== 'undefined' && helpCenterI18n.try_general_keywords ? helpCenterI18n.try_general_keywords : booknetic.__('Try using more general keywords')}</div>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    `;
                                    searchResultsContainer.html(noResultsHtml).show();
                                    noResultsContainer.hide(); // Hide the separate no results container
                                }
                            },
                            error: function(xhr, status, error) {
                                errorMessage.text('Error: ' + error).show();
                            },
                            complete: function() {
                                loadingIndicator.hide();
                                $(".search-box").removeClass("searching");
                            }
                        });
                    }, debounceTime);
                }
            });
            
            // Add loading animation with enhanced styling
            loadingIndicator.html(`
                <div class="loading-content">
                    <i class="fas fa-circle-notch fa-spin"></i>
                    <span>${typeof helpCenterI18n !== 'undefined' && helpCenterI18n.searching ? helpCenterI18n.searching : booknetic.__('Searching...')}</span>
                </div>
            `);
            
            // Add search icon to search button if not already present
            if ($('.secondary-button-help').find('i').length === 0) {
                $('.secondary-button-help').prepend('<i class="fas fa-search"></i>');
            }
            
            // Show popular searches when input is focused and empty
            searchInput.on("focus", function() {
                const searchTerm = $(this).val().trim();
                if (searchTerm.length === 0) {
                    fetchPopularSearches();
                    noResultsContainer.hide();
                    searchResultsContainer.hide();
                    
                    // Add subtle animation to search box on focus
                    $(".search-box").addClass("focused");
                }
            });
            
            searchInput.on("blur", function() {
                $(".search-box").removeClass("focused");
            });
            
            // Handle Enter key press
            searchInput.on("keypress", function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $("#helpCenterSearchForm").submit();
                }
            });
            
            // Close suggestions when clicking the close button
            closeButton.on("click", function() {
                hideSearchSuggestions();
            });
            
            // Close suggestions when clicking outside
            $(document).on("click", function(e) {
                if (!$(e.target).closest('.search-container').length && !$(e.target).closest('.autocomplete-suggestions').length) {
                    hideSearchSuggestions();
                }
            });
            
            // Prevent clicks inside suggestions from closing
            suggestionsContainer.on("click", function(e) {
                e.stopPropagation();
            });
            
            // Functions to show/hide suggestions with animation
            function showSearchSuggestions() {
                suggestionsContainer.css('display', 'block');
                setTimeout(() => {
                    suggestionsContainer.addClass('visible');
                }, 10);
                
                // Add icons to header if not already present
                if ($('.search-results-header h4').find('i').length === 0) {
                    $('.search-results-header h4').prepend('<i class="fas fa-search"></i>');
                }
                
                $(document).on('click.suggestions', function(e) {
                    if (!$(e.target).closest('.search-container').length) {
                        hideSearchSuggestions();
                    }
                });
            }
            
            function hideSearchSuggestions() {
                suggestionsContainer.removeClass('visible');
                setTimeout(() => {
                    suggestionsContainer.css('display', 'none');
                }, 300);
                $(document).off('click.suggestions');
            }

            // This function is now simplified since we're showing no results directly in the search results container
            function showNoResults(searchTerm) {
                // This function is kept for backward compatibility but is no longer used
                // No results are now shown directly in the search results container
            }
            
            // Use the booknetic.__ function for translations
            function bkntc__(text) {
                return booknetic.__(text);
            }
        });
        
        // Topic feedback AJAX functionality
        $(document).on('click', '.feedback-button', function() {
            const button = $(this);
            const feedbackContainer = button.closest('.feedback-container');
            const messageContainer = feedbackContainer.find('.feedback-message');
            const feedbackValue = button.data('feedback');
            const topicId = button.data('topic-id');
            const nonce = $('#feedback_nonce').val();
            
            // Disable buttons during submission
            feedbackContainer.find('.feedback-button').prop('disabled', true);
            
            // Get AJAX URL and nonce from helpCenterAjax global object
            const ajaxUrl = typeof helpCenterAjax !== 'undefined' ? helpCenterAjax.ajaxUrl : ajaxurl;
            
            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: {
                    action: 'booknetic_topic_feedback',
                    _wpnonce: nonce || (typeof helpCenterAjax !== 'undefined' ? helpCenterAjax.nonce : ''),
                    topic_id: topicId,
                    feedback: feedbackValue
                },
                beforeSend: function() {
                    // Show loading indicator
                    messageContainer.html('<i class="fas fa-spinner fa-spin"></i> ' + booknetic.__('Processing...'));
                    messageContainer.removeClass('error').addClass('loading').show();
                },
                success: function(response) {
                    try {
                        // Parse response if it's a string
                        if (typeof response === 'string') {
                            response = JSON.parse(response);
                        }
                        
                        // Check status and handle success
                        if (response.success) {
                            // Replace feedback container with success message
                            feedbackContainer.html('<div class="feedback-success-message">' + response.data.message + '</div>');
                            
                            // Update stats display if present
                            if (response.data.stats_html) {
                                $('.feedback-stats').html(response.data.stats_html);
                            }
                            
                            // Show success toast if booknetic.toast is available
                            if (typeof booknetic !== 'undefined' && typeof booknetic.toast === 'function') {
                                booknetic.toast(response.data.message, 'success');
                            }
                        } else {
                            // Show error message
                            messageContainer.html(response.data.error || booknetic.__('An error occurred'));
                            messageContainer.removeClass('loading').addClass('error');
                            
                            // Re-enable buttons
                            feedbackContainer.find('.feedback-button').prop('disabled', false);
                            
                            // Show error toast if booknetic.toast is available
                            if (typeof booknetic !== 'undefined' && typeof booknetic.toast === 'function') {
                                booknetic.toast(response.data.error || booknetic.__('An error occurred'), 'error');
                            }
                        }
                    } catch (e) {
                        // Handle parsing errors
                        messageContainer.html(booknetic.__('Invalid response from server'));
                        messageContainer.removeClass('loading').addClass('error');
                        
                        // Re-enable buttons
                        feedbackContainer.find('.feedback-button').prop('disabled', false);
                        
                        // Show error toast if booknetic.toast is available
                        if (typeof booknetic !== 'undefined' && typeof booknetic.toast === 'function') {
                            booknetic.toast(booknetic.__('Invalid response from server'), 'error');
                        }
                    }
                },
                error: function() {
                    // Handle AJAX errors
                    messageContainer.html(booknetic.__('Failed to process request'));
                    messageContainer.removeClass('loading').addClass('error');
                    
                    // Re-enable buttons
                    feedbackContainer.find('.feedback-button').prop('disabled', false);
                    
                    // Show error toast if booknetic.toast is available
                    if (typeof booknetic !== 'undefined' && typeof booknetic.toast === 'function') {
                        booknetic.toast(booknetic.__('Failed to process request'), 'error');
                    }
                },
                complete: function() {
                    // Any cleanup needed after the request completes
                }
            });
        });
    });
})(jQuery);
