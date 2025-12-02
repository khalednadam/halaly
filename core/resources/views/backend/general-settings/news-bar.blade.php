@extends('backend.admin-master')
@section('site-title')
    {{__('News Bar Settings')}}
@endsection
@section('style')
    <x-media.css/>
    <style>
        .color-picker-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .color-picker-input {
            width: 60px;
            height: 40px;
            border: 1px solid #ddd;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
@endsection
@section('content')
<div class="row g-4 mt-0">
    <div class="col-xl-12 col-lg-12 mt-0">
        <div class="dashboard__card bg__white padding-20 radius-10">
            <h2 class="dashboard__card__header__title mb-3">{{__('News Bar Settings')}}</h2>
            <x-validation.error/>
            <form action="{{route('admin.general.news.bar.settings')}}" method="POST" class="validateForm" enctype="multipart/form-data">
                @csrf
                <div class="form__input__flex">
                    <div class="form__input__single d-grid">
                        <label for="news_bar_status"><strong>{{__('Enable News Bar')}}</strong></label>
                        <div class="switch_box style_7">
                            <input type="checkbox" name="news_bar_status" id="news_bar_status" @if(get_static_option('news_bar_status') === 'on') checked @endif value="on">
                            <label></label>
                        </div>
                    </div>

                    @foreach($all_languages as $lang)
                        <div class="form__input__single">
                            <label for="news_bar_text_{{ $lang->slug }}" class="form__input__single__label">
                                {{ __('News Bar Text') }} ({{ $lang->name }})
                            </label>
                            <input type="text" 
                                   name="news_bar_text_{{ $lang->slug }}" 
                                   id="news_bar_text_{{ $lang->slug }}" 
                                   value="{{ get_static_option('news_bar_text_' . $lang->slug) }}" 
                                   class="form__control radius-5"
                                   placeholder="{{ __('Enter news bar text for') }} {{ $lang->name }}">
                        </div>
                    @endforeach

                    <div class="form__input__single">
                        <label for="news_bar_bg_color" class="form__input__single__label">{{ __('Background Color') }}</label>
                        <div class="color-picker-wrapper">
                            <input type="color" 
                                   name="news_bar_bg_color" 
                                   id="news_bar_bg_color" 
                                   value="{{ get_static_option('news_bar_bg_color') ?: '#f8f9fa' }}" 
                                   class="color-picker-input">
                            <input type="text" 
                                   value="{{ get_static_option('news_bar_bg_color') ?: '#f8f9fa' }}" 
                                   class="form__control radius-5" 
                                   placeholder="#f8f9fa"
                                   onchange="document.getElementById('news_bar_bg_color').value = this.value">
                        </div>
                    </div>

                    <div class="form__input__single">
                        <label for="news_bar_text_color" class="form__input__single__label">{{ __('Text Color') }}</label>
                        <div class="color-picker-wrapper">
                            <input type="color" 
                                   name="news_bar_text_color" 
                                   id="news_bar_text_color" 
                                   value="{{ get_static_option('news_bar_text_color') ?: '#333333' }}" 
                                   class="color-picker-input">
                            <input type="text" 
                                   value="{{ get_static_option('news_bar_text_color') ?: '#333333' }}" 
                                   class="form__control radius-5" 
                                   placeholder="#333333"
                                   onchange="document.getElementById('news_bar_text_color').value = this.value">
                        </div>
                    </div>
                </div>
                <div class="btn_wrapper mt-4">
                    <button type="submit" id="update" class="cmnBtn btn_5 btn_bg_blue radius-5">{{ __('Update Changes') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
<x-media.markup/>
@endsection
@section('scripts')
    <script>
        (function($){
            "use strict";
            $(document).ready(function(){
                <x-btn.update/>
                // Sync color picker inputs
                $('#news_bar_bg_color').on('change', function() {
                    $(this).next('input[type="text"]').val($(this).val());
                });
                $('#news_bar_text_color').on('change', function() {
                    $(this).next('input[type="text"]').val($(this).val());
                });
                $('input[type="text"]').on('change', function() {
                    if ($(this).prev('input[type="color"]').length) {
                        $(this).prev('input[type="color"]').val($(this).val());
                    }
                });
            });
        }(jQuery));
    </script>
@endsection

