@php
    $current_lang = get_user_lang();
    $news_bar_text = get_static_option('news_bar_text_' . $current_lang);
    $news_bar_status = get_static_option('news_bar_status');
    $news_bar_bg_color = get_static_option('news_bar_bg_color') ?: '#f8f9fa';
    $news_bar_text_color = get_static_option('news_bar_text_color') ?: '#333333';
@endphp

@if($news_bar_status === 'on' && !empty($news_bar_text))
    <div class="news-bar-container" style="background-color: {{ $news_bar_bg_color }}; color: {{ $news_bar_text_color }};">
        <div class="news-bar-marquee">
            <div class="news-bar-track" id="newsBarTrack">
                @for ($i = 0; $i < 20; $i++)
                    <span class="news-bar-item">{{ $news_bar_text }}</span>
                @endfor
            </div>
        </div>
    </div>

    <style>
        .news-bar-container {
            width: 100%;
            position: sticky;
            top: 0;
            z-index: 9999;
            padding: 12px 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .news-bar-marquee {
            width: 100%;
            overflow: hidden;
            position: relative;
        }

        .news-bar-track {
            display: inline-flex;
            white-space: nowrap;
            will-change: transform;
        }

        .news-bar-item {
            display: inline-block;
            font-size: 14px;
            font-weight: 500;
            line-height: 1.5;
            padding: 0 50px;
            flex-shrink: 0;
        }

        /* Pause on hover with smooth transition */
        .news-bar-container:hover .news-bar-track {
            animation-play-state: paused;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .news-bar-item {
                font-size: 12px;
                padding: 0 30px;
            }
        }

        @media (max-width: 480px) {
            .news-bar-item {
                font-size: 11px;
                padding: 0 20px;
            }
        }
    </style>

    <script>
        (function () {
            const track = document.getElementById('newsBarTrack');
            if (!track) return;

            const speed = 50; // pixels per second
            let animationId;
            let currentPosition = 0;
            let itemWidth = 0;

            function setupMarquee() {
                // Cancel any existing animation
                if (animationId) {
                    cancelAnimationFrame(animationId);
                }

                const containerWidth = track.parentElement.offsetWidth;
                const firstItem = track.querySelector('.news-bar-item');
                itemWidth = firstItem.offsetWidth;

                // Clear existing items except first
                track.innerHTML = '';
                track.appendChild(firstItem);

                // Calculate how many duplicates we need to fill screen + buffer
                const itemsNeeded = Math.ceil((containerWidth * 2) / itemWidth) + 2;
                const itemText = firstItem.textContent;

                // Create duplicates
                for (let i = 1; i < itemsNeeded; i++) {
                    const newItem = document.createElement('span');
                    newItem.className = 'news-bar-item';
                    newItem.textContent = itemText;
                    track.appendChild(newItem);
                }

                // Reset position
                currentPosition = 0;

                // Start animation
                animate();
            }

            function animate() {
                const pixelsPerFrame = speed / 60; // 60 FPS
                currentPosition += pixelsPerFrame;

                // Reset position when one complete item has scrolled past
                if (currentPosition >= itemWidth) {
                    currentPosition -= itemWidth;
                }

                track.style.transform = `translateX(${currentPosition}px)`;
                animationId = requestAnimationFrame(animate);
            }

            // Pause on hover
            track.parentElement.addEventListener('mouseenter', function () {
                if (animationId) {
                    cancelAnimationFrame(animationId);
                }
            });

            track.parentElement.addEventListener('mouseleave', function () {
                animate();
            });

            // Setup on load
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', setupMarquee);
            } else {
                setupMarquee();
            }

            // Recalculate on window resize
            let resizeTimer;
            window.addEventListener('resize', function () {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(setupMarquee, 250);
            });
        })();
    </script>
@endif
