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
            <div class="news-bar-content">
                @for($i = 0; $i < 10; $i++)
                    <span class="news-bar-item">{{ $news_bar_text }}
                    <span class="news-bar-item"> - </span>
                @endfor
            </div>
        </div>
    </div>
    <style>
        .news-bar-container {
            width: 100%;
            position: sticky;
            top: 0;
            left: 0;
            right: 0;
            z-index: 9999;
            padding: 12px 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        .news-bar-marquee {
            width: 100%;
            overflow: hidden;
            position: relative;
            white-space: nowrap;
        }
        .news-bar-content {
            display: inline-flex;
            white-space: nowrap;
            animation: marquee-scroll 60s linear infinite;
            will-change: transform;
            width: fit-content;
        }
        .news-bar-item {
            display: inline-block;
            font-size: 14px;
            font-weight: 500;
            line-height: 1.5;
            padding: 0 50px;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .news-separator {
            display: inline-block;
            padding: 0 15px;
            opacity: 0.6;
            font-weight: normal;
        }
        @keyframes marquee-scroll {
            from {
                transform: translateX(0);
            }
            to {
                transform: translateX(-50%);
            }
        }
        .news-bar-container:hover .news-bar-content {
            animation-play-state: paused;
        }
        [dir="rtl"] .news-bar-content {
            animation: marquee-scroll-rtl 60s linear infinite;
        }
        @keyframes marquee-scroll-rtl {
            from {
                transform: translateX(0);
            }
            to {
                transform: translateX(50%);
            }
        }
        @media (max-width: 768px) {
            .news-bar-container {
                padding: 10px 0;
            }
            .news-bar-item {
                font-size: 12px;
                padding: 0 30px;
            }
            .news-separator {
                padding: 0 10px;
            }
            .news-bar-content {
                animation-duration: 50s;
            }
            [dir="rtl"] .news-bar-content {
                animation-duration: 50s;
            }
        }
    </style>
@endif

