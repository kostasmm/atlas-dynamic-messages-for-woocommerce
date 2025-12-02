jQuery(document).ready(function ($) {
    // Adding support for multiple languages
    // Initialize day abbreviations for various languages
    const localizedDays = {
        'el': ['Κυρ', 'Δευ', 'Τρί', 'Τετ', 'Πέμ', 'Παρ', 'Σάβ'],
        'en': ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
        'fr': ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'],
        'de': ['Son', 'Mon', 'Die', 'Mit', 'Don', 'Fre', 'Sam'],
        'es': ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'],
        'it': ['Dom', 'Lun', 'Mar', 'Mer', 'Gio', 'Ven', 'Sab'],
        'pt': ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'],
        'ru': ['Вск', 'Пнд', 'Втр', 'Срд', 'Чтв', 'Птн', 'Сбт'],
        'nl': ['Zon', 'Maa', 'Din', 'Woe', 'Don', 'Vri', 'Zat'],
        'pl': ['Nie', 'Pon', 'Wto', 'Śro', 'Czw', 'Pią', 'Sob'],
        'tr': ['Paz', 'Pts', 'Sal', 'Çar', 'Per', 'Cum', 'Cts'],
        'sv': ['Sön', 'Mån', 'Tis', 'Ons', 'Tor', 'Fre', 'Lör'],
        'da': ['Søn', 'Man', 'Tir', 'Ons', 'Tor', 'Fre', 'Lør'],
        'fi': ['Sun', 'Maa', 'Tii', 'Kes', 'Tor', 'Per', 'Lau'],
        'no': ['Søn', 'Man', 'Tir', 'Ons', 'Tor', 'Fre', 'Lør'],
        'cs': ['Ned', 'Pon', 'Úte', 'Stř', 'Čtv', 'Pát', 'Sob'],
        'hu': ['Vas', 'Hét', 'Ked', 'Sze', 'Csü', 'Pén', 'Szo'],
        'ro': ['Dum', 'Lun', 'Mar', 'Mie', 'Joi', 'Vin', 'Sâm'],
        'hr': ['Ned', 'Pon', 'Uto', 'Sri', 'Čet', 'Pet', 'Sub'],
        'bg': ['Нед', 'Пон', 'Вто', 'Сря', 'Чет', 'Пет', 'Съб']
    };

    // Helper function to get the correct day abbreviation
    function getLocalizedDayLabel(dayNumber) {
        // Get current language from HTML
        const lang = document.documentElement.lang.substring(0, 2).toLowerCase();

        // If there's a translation for the current language, use it
        if (localizedDays[lang] && localizedDays[lang][dayNumber] !== undefined) {
            return localizedDays[lang][dayNumber];
        }

        // Otherwise use English abbreviations
        return null;
    }

    // Function to apply translated day labels
    function applyDayLabels() {
        // Get current language
        const lang = document.documentElement.lang.substring(0, 2).toLowerCase();

        // Check if language is supported
        if (localizedDays[lang]) {
            $('.atlas-dmsg-day-button').each(function () {
                const dayValue = parseInt($(this).find('input').val());
                $(this).find('span').text(localizedDays[lang][dayValue]);
            });
        }
    }

    /**
     * Check if a time range crosses midnight and show appropriate notification
     * 
     * @param {string} startTime Start time in HH:MM format
     * @param {string} endTime End time in HH:MM format
     * @param {jQuery} $container The scenario container element
     */
    function checkMidnightCrossing(startTime, endTime, $container) {
        // Don't proceed if either time is not set
        if (!startTime || !endTime) return;

        // Convert times to minutes for easier comparison
        const [startHour, startMinute] = startTime.split(':').map(Number);
        const [endHour, endMinute] = endTime.split(':').map(Number);

        const startTotalMinutes = startHour * 60 + startMinute;
        const endTotalMinutes = endHour * 60 + endMinute;

        // Check if the time range crosses midnight
        const crossesMidnight = endTotalMinutes < startTotalMinutes;

        // Find or create notification element
        let $notification = $container.find('.atlas-dmsg-midnight-notice');
        if ($notification.length === 0) {
            $notification = $('<div class="atlas-dmsg-midnight-notice"></div>');
            $container.find('.atlas-dmsg-time-inputs').after($notification);
        }

        if (crossesMidnight) {
            // Use translated strings instead of hardcoded text
            $notification.html('<div class="notice notice-info inline"><p><strong>' +
                atlasDmsgAdmin.strings.note + ':</strong> ' +
                atlasDmsgAdmin.strings.midnight_crossing_notice + '</p></div>');

            // Highlight the day selector to hint user to check day selection
            $container.find('.atlas-dmsg-days-selector').addClass('atlas-dmsg-highlight-days');
        } else {
            // Remove the notification if time range no longer crosses midnight
            $notification.empty();
            $container.find('.atlas-dmsg-days-selector').removeClass('atlas-dmsg-highlight-days');
        }
    }

    // Initialize tabs
    $("#atlas-dmsg-tabs").tabs({
        active: localStorage.getItem('atlas_dmsg_active_tab') || 0,
        activate: function (event, ui) {
            localStorage.setItem('atlas_dmsg_active_tab', ui.newTab.index());
        }
    });

    // Initialize layout tabs
    $(".atlas-dmsg-layouts-tabs").tabs();

    // Add additional CSS for consistent previews
    const additionalCSS = `
    /* Fix for Gradient Alert Preview Boxes */
    .atlas-dmsg-preview-box.atlas-dmsg-layout-3 {
        position: relative;
        overflow: hidden;
        padding-top: 20px; /* Ensure there's space for the accent bar */
    }

    .atlas-dmsg-preview-box.atlas-dmsg-layout-3::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        z-index: 1;
    }

    /* Ensure all preview boxes have position context for absolute elements */
    .atlas-dmsg-preview-box {
        position: relative;
    }
    `;
    $('head').append('<style id="atlas-dmsg-additional-css">' + additionalCSS + '</style>');

    // Initialize color pickers
    $('.atlas-dmsg-color-picker').wpColorPicker({
        change: function (event, ui) {
            updateLayoutPreview($(this));
        }
    });

    // Update layout preview when inputs change
    $('.atlas-dmsg-layout-settings-panel input, .atlas-dmsg-layout-settings-panel select, .atlas-dmsg-layout-settings-panel textarea').on('change input', function () {
        updateLayoutPreview($(this));
    });

    // Function to update layout preview
    function updateLayoutPreview(changedElement) {
        // Get layout number from the panel ID
        var panelId = changedElement.closest('.atlas-dmsg-layout-settings-panel').attr('id');
        var layoutNum = panelId.replace('layout-', '').replace('-settings', '');
        var preview = $('.atlas-dmsg-layout-' + layoutNum + '-preview');

        // Create style element if it doesn't exist
        var styleId = 'atlas-dmsg-preview-style-' + layoutNum;
        if ($('#' + styleId).length === 0) {
            $('head').append('<style id="' + styleId + '"></style>');
        }

        var cssRules = '';

        // Apply styles based on layout type
        if (layoutNum === '1') {
            // Layout 1 - Dashed Style
            var bgColor = $('input[name="atlas_dmsg_settings[layouts][1][bg_color]"]').val() || '#fef9e7';
            var textColor = $('input[name="atlas_dmsg_settings[layouts][1][text_color]"]').val() || '#4b2900';
            var borderColor = $('input[name="atlas_dmsg_settings[layouts][1][border_color]"]').val() || '#ffa800';
            var accentColor = $('input[name="atlas_dmsg_settings[layouts][1][accent_color]"]').val() || '#b53300';
            var borderStyle = $('select[name="atlas_dmsg_settings[layouts][1][border_style]"]').val() || 'dashed';
            var icon = $('input[name="atlas_dmsg_settings[layouts][1][icon]"]').val();

            cssRules = '.atlas-dmsg-layout-1-preview, .atlas-dmsg-preview-box.atlas-dmsg-layout-1 { background-color: ' + bgColor + '; color: ' + textColor + '; border-left: 2px ' + borderStyle + ' ' + borderColor + '; border-right: 2px ' + borderStyle + ' ' + borderColor + '; border-bottom: 2px ' + borderStyle + ' ' + borderColor + '; }';
            cssRules += '.atlas-dmsg-layout-1-preview::before, .atlas-dmsg-preview-box.atlas-dmsg-layout-1::before { background-color: ' + borderColor + '; }';
            cssRules += '.atlas-dmsg-layout-1-preview strong, .atlas-dmsg-preview-box.atlas-dmsg-layout-1 strong { color: ' + accentColor + '; }';

            if (icon) {
                cssRules += '.atlas-dmsg-layout-1-preview::after, .atlas-dmsg-preview-box.atlas-dmsg-layout-1::after { content: "' + icon + '"; position: absolute; top: 5px; right: 10px; font-size: 18px; }';
            }

        } else if (layoutNum === '2') {
            // Layout 2 - Modern Card
            var bgColor = $('input[name="atlas_dmsg_settings[layouts][2][bg_color]"]').val() || '#ffffff';
            var textColor = $('input[name="atlas_dmsg_settings[layouts][2][text_color]"]').val() || '#2c3e50';
            var borderColor = $('input[name="atlas_dmsg_settings[layouts][2][border_color]"]').val() || '#2c3e50';
            var accentColor = $('input[name="atlas_dmsg_settings[layouts][2][accent_color]"]').val() || '#2980b9';
            var shadowIntensity = $('select[name="atlas_dmsg_settings[layouts][2][shadow_intensity]"]').val() || 'medium';
            var icon = $('input[name="atlas_dmsg_settings[layouts][2][icon]"]').val() || '🚚';
            var borderRadius = $('input[name="atlas_dmsg_settings[layouts][2][border_radius]"]').val() || '8';

            var shadowStyle = '';
            if (shadowIntensity === 'light') {
                shadowStyle = '0 2px 4px rgba(0, 0, 0, 0.05)';
            } else if (shadowIntensity === 'medium') {
                shadowStyle = '0 4px 6px rgba(0, 0, 0, 0.1)';
            } else {
                shadowStyle = '0 6px 10px rgba(0, 0, 0, 0.15)';
            }

            cssRules = '.atlas-dmsg-layout-2-preview, .atlas-dmsg-preview-box.atlas-dmsg-layout-2 { background-color: ' + bgColor + '; color: ' + textColor + '; border: 2px solid ' + borderColor + '; border-radius: ' + borderRadius + 'px; box-shadow: ' + shadowStyle + '; }';
            cssRules += '.atlas-dmsg-layout-2-preview strong, .atlas-dmsg-preview-box.atlas-dmsg-layout-2 strong { color: ' + accentColor + '; }';
            cssRules += '.atlas-dmsg-layout-2-preview::before, .atlas-dmsg-preview-box.atlas-dmsg-layout-2::before { content: "' + icon + '"; font-size: 24px; display: block; text-align: center; margin-bottom: 10px; }';

        } else if (layoutNum === '3') {
            // Layout 3 - Gradient Alert
            var gradientStart = $('input[name="atlas_dmsg_settings[layouts][3][gradient_start]"]').val() || '#ffe8d4';
            var gradientEnd = $('input[name="atlas_dmsg_settings[layouts][3][gradient_end]"]').val() || '#ffd8b2';
            var gradientDirection = $('select[name="atlas_dmsg_settings[layouts][3][gradient_direction]"]').val() || '135deg';
            var textColor = $('input[name="atlas_dmsg_settings[layouts][3][text_color]"]').val() || '#d32f2f';
            var borderColor = $('input[name="atlas_dmsg_settings[layouts][3][border_color]"]').val() || '#ff6b6b';
            var accentColor = $('input[name="atlas_dmsg_settings[layouts][3][accent_color]"]').val() || '#ff6b6b';
            var icon = $('input[name="atlas_dmsg_settings[layouts][3][icon]"]').val();
            var accentHeight = $('input[name="atlas_dmsg_settings[layouts][3][accent_height]"]').val() || '4';

            // Updated CSS for Layout 3 to ensure consistent display in all previews
            cssRules = '.atlas-dmsg-layout-3-preview, .atlas-dmsg-preview-box.atlas-dmsg-layout-3 { background: linear-gradient(' + gradientDirection + ', ' + gradientStart + ', ' + gradientEnd + '); color: ' + textColor + '; border: 2px solid ' + borderColor + '; position: relative; overflow: hidden; padding-top: 20px; }';
            cssRules += '.atlas-dmsg-layout-3-preview::before, .atlas-dmsg-preview-box.atlas-dmsg-layout-3::before { content: ""; position: absolute; top: 0; left: 0; width: 100%; height: ' + accentHeight + 'px; background: ' + accentColor + '; z-index: 1; }';
            cssRules += '.atlas-dmsg-layout-3-preview strong, .atlas-dmsg-preview-box.atlas-dmsg-layout-3 strong { color: ' + textColor + '; }';

            if (icon) {
                cssRules += '.atlas-dmsg-layout-3-preview::after, .atlas-dmsg-preview-box.atlas-dmsg-layout-3::after { content: "' + icon + '"; position: absolute; top: 10px; right: 10px; font-size: 18px; z-index: 2; }';
            }
        }

        // Add custom CSS if provided
        var customCSS = $('textarea[name="atlas_dmsg_settings[layouts][' + layoutNum + '][custom_css]"]').val();
        if (customCSS) {
            cssRules += customCSS;
        }

        // Update the style element
        $('#' + styleId).html(cssRules);

        // Update all scenario preview boxes that use this layout
        updateScenarioPreviewsWithLayout(layoutNum);
    }

    // Helper function to generate localized time preview
    function getLocalizedTimePreview() {
        return '<strong>2 ' + atlasDmsgAdmin.strings.hours + atlasDmsgAdmin.strings.comma + ' 30 ' +
            atlasDmsgAdmin.strings.minutes + ' ' + atlasDmsgAdmin.strings.and + ' 45 ' +
            atlasDmsgAdmin.strings.seconds + '</strong>';
    }

    // Central function to update all previews
    function updateAllPreviews() {
        $('.atlas-dmsg-message-input').each(function () {
            const message = $(this).val();
            const previewBox = $(this).siblings('.atlas-dmsg-message-preview').find('.atlas-dmsg-preview-box');

            // Replace placeholder with localized time
            const formattedMessage = message.replace(/{time_remain}/g, getLocalizedTimePreview());

            // Add paragraph tags for better formatting
            if (formattedMessage) {
                previewBox.html('<p>' + formattedMessage + '</p>');
            } else {
                previewBox.html('<p>' + (atlasDmsgAdmin.strings.time_placeholder || 'Use {time_remain} placeholder to show countdown.') + '</p>');
            }
        });
    }

    // Function to update all scenario previews that use a specific layout
    function updateScenarioPreviewsWithLayout(layoutNum) {
        // Update only previews with the specific layout
        $('.atlas-dmsg-preview-box.atlas-dmsg-layout-' + layoutNum).each(function () {
            const $previewBox = $(this);
            const $messageInput = $previewBox.closest('.atlas-dmsg-scenario-content').find('.atlas-dmsg-message-input');

            const message = $messageInput.val();
            const formattedMessage = message.replace(/{time_remain}/g, getLocalizedTimePreview());

            if (formattedMessage) {
                $previewBox.html('<p>' + formattedMessage + '</p>');
            } else {
                $previewBox.html('<p>' + (atlasDmsgAdmin.strings.time_placeholder || 'Use {time_remain} placeholder to show countdown.') + '</p>');
            }
        });
    }

    // Reset layout to default settings
    $('.atlas-dmsg-reset-layout').on('click', function () {
        // Convert the data-layout attribute to a number for proper comparison
        const layoutId = parseInt($(this).data('layout'), 10);

        // Confirm before resetting
        if (!confirm(atlasDmsgAdmin.strings.confirmResetLayout)) {
            return;
        }

        // Reset settings based on layout ID
        if (layoutId === 1) {
            // Dashed Style defaults
            $('input[name="atlas_dmsg_settings[layouts][1][bg_color]"]').wpColorPicker('color', '#fef9e7');
            $('input[name="atlas_dmsg_settings[layouts][1][text_color]"]').wpColorPicker('color', '#4b2900');
            $('input[name="atlas_dmsg_settings[layouts][1][border_color]"]').wpColorPicker('color', '#ffa800');
            $('input[name="atlas_dmsg_settings[layouts][1][accent_color]"]').wpColorPicker('color', '#b53300');
            $('select[name="atlas_dmsg_settings[layouts][1][border_style]"]').val('dashed').trigger('change');
            $('input[name="atlas_dmsg_settings[layouts][1][icon]"]').val('').trigger('change');
            $('textarea[name="atlas_dmsg_settings[layouts][1][custom_css]"]').val('').trigger('change');

        } else if (layoutId === 2) {
            // Modern Card defaults
            $('input[name="atlas_dmsg_settings[layouts][2][bg_color]"]').wpColorPicker('color', '#ffffff');
            $('input[name="atlas_dmsg_settings[layouts][2][text_color]"]').wpColorPicker('color', '#2c3e50');
            $('input[name="atlas_dmsg_settings[layouts][2][border_color]"]').wpColorPicker('color', '#2c3e50');
            $('input[name="atlas_dmsg_settings[layouts][2][accent_color]"]').wpColorPicker('color', '#2980b9');
            $('select[name="atlas_dmsg_settings[layouts][2][shadow_intensity]"]').val('medium').trigger('change');
            $('input[name="atlas_dmsg_settings[layouts][2][icon]"]').val('🚚').trigger('change');
            $('input[name="atlas_dmsg_settings[layouts][2][border_radius]"]').val(8).trigger('change');
            $('input[name="atlas_dmsg_settings[layouts][2][border_radius]"]').next('output').text('8px');
            $('textarea[name="atlas_dmsg_settings[layouts][2][custom_css]"]').val('').trigger('change');

        } else if (layoutId === 3) {
            // Gradient Alert defaults
            $('input[name="atlas_dmsg_settings[layouts][3][gradient_start]"]').wpColorPicker('color', '#ffe8d4');
            $('input[name="atlas_dmsg_settings[layouts][3][gradient_end]"]').wpColorPicker('color', '#ffd8b2');
            $('input[name="atlas_dmsg_settings[layouts][3][text_color]"]').wpColorPicker('color', '#d32f2f');
            $('input[name="atlas_dmsg_settings[layouts][3][border_color]"]').wpColorPicker('color', '#ff6b6b');
            $('input[name="atlas_dmsg_settings[layouts][3][accent_color]"]').wpColorPicker('color', '#ff6b6b');
            $('select[name="atlas_dmsg_settings[layouts][3][gradient_direction]"]').val('135deg').trigger('change');
            $('input[name="atlas_dmsg_settings[layouts][3][icon]"]').val('').trigger('change');
            $('input[name="atlas_dmsg_settings[layouts][3][accent_height]"]').val(4).trigger('change');
            $('input[name="atlas_dmsg_settings[layouts][3][accent_height]"]').next('output').text('4px');
            $('textarea[name="atlas_dmsg_settings[layouts][3][custom_css]"]').val('').trigger('change');
        }

        // Update the preview (use any field to trigger the update)
        updateLayoutPreview($(`input[name="atlas_dmsg_settings[layouts][${layoutId}][bg_color]"]`));

        // Show reset confirmation message
        const $panel = $(this).closest('.atlas-dmsg-layout-settings-panel');
        const $message = $('<div>').addClass('notice notice-success inline').html('<p>' + atlasDmsgAdmin.strings.layoutReset + '</p>');

        // Add message, remove after 3 seconds
        $panel.find('.notice').remove();  // Remove any existing notices
        $panel.prepend($message);
        setTimeout(function () {
            $message.fadeOut(500, function () {
                $(this).remove();
            });
        }, 3000);
    });

    // Scenario counter (used for adding new scenarios)
    var scenarioIndex = atlasDmsgAdmin.scenarioCount;

    // Add new scenario
    document.getElementById('add-scenario').addEventListener('click', function () {
        var template = $('#atlas-dmsg-scenario-template').html();
        var newScenario = template
            .replace(/\{\{index\}\}/g, scenarioIndex)
            .replace(/\{\{number\}\}/g, scenarioIndex + 1);

        $('#atlas-dmsg-scenarios-container').append(newScenario);

        // Apply translated day labels to new scenario
        applyDayLabels();

        // Increment scenario index
        scenarioIndex++;

        // Update all scenario numbers
        updateScenarioNumbers();

        // Scroll to the new scenario
        $('html, body').animate({
            scrollTop: $(`#atlas-dmsg-scenarios-container .atlas-dmsg-scenario-box[data-index="${scenarioIndex - 1}"]`).offset().top - 100
        }, 500);
    });

    // Remove scenario
    $('#atlas-dmsg-scenarios-container').on('click', '.remove-scenario', function () {
        if (confirm(atlasDmsgAdmin.strings.confirm_remove)) {
            $(this).closest('.atlas-dmsg-scenario-box').fadeOut(300, function () {
                $(this).remove();
                updateScenarioNumbers();
            });
        }
    });

    // Layout selection in each scenario
    $(document).on('change', '.atlas-dmsg-layout-option input[type="radio"]', function () {
        var $option = $(this).closest('.atlas-dmsg-layout-option');
        var $allOptions = $option.closest('.atlas-dmsg-layout-selector').find('.atlas-dmsg-layout-option');

        $allOptions.removeClass('selected');
        $option.addClass('selected');

        // Update preview box for this scenario
        var selectedLayout = $(this).val();
        var $scenarioBox = $(this).closest('.atlas-dmsg-scenario-box');
        $scenarioBox.find('.atlas-dmsg-preview-box').attr('class', 'atlas-dmsg-preview-box atlas-dmsg-layout-' + selectedLayout);

        // Update the message preview content
        const $messageInput = $scenarioBox.find('.atlas-dmsg-message-input');
        $messageInput.trigger('input');
    });

    // Call these functions at appropriate places
    $(document).on('input', '.atlas-dmsg-message-input', updateAllPreviews);

    // Day buttons styling
    $(document).on('change', '.atlas-dmsg-day-button input', function () {
        if ($(this).is(':checked')) {
            $(this).parent().addClass('selected');
        } else {
            $(this).parent().removeClass('selected');
        }
    });

    // Initialize day buttons on page load
    $('.atlas-dmsg-day-button input').each(function () {
        if ($(this).is(':checked')) {
            $(this).parent().addClass('selected');
        }
    });

    // Time offset change - update preview
    $('#time-offset').on('input change', function () {
        var offset = parseInt($(this).val()) || 0;
        var now = new Date();

        // Apply offset
        now.setHours(now.getHours() + offset);

        // Format time
        var hours = String(now.getHours()).padStart(2, '0');
        var minutes = String(now.getMinutes()).padStart(2, '0');
        var seconds = String(now.getSeconds()).padStart(2, '0');

        // Update display
        $('#atlas-dmsg-server-time').text(`${hours}:${minutes}:${seconds}`);
    });

    // Monitor time input changes to detect midnight crossing
    $(document).on('change input', '.atlas-dmsg-time-input', function () {
        const $scenarioBox = $(this).closest('.atlas-dmsg-scenario-box');
        const startTime = $scenarioBox.find('input[name*="[start_time]"]').val();
        const endTime = $scenarioBox.find('input[name*="[end_time]"]').val();

        checkMidnightCrossing(startTime, endTime, $scenarioBox);
    });

    // Also check existing scenarios when page loads
    $('.atlas-dmsg-scenario-box').each(function () {
        const $scenarioBox = $(this);
        const startTime = $scenarioBox.find('input[name*="[start_time]"]').val();
        const endTime = $scenarioBox.find('input[name*="[end_time]"]').val();

        checkMidnightCrossing(startTime, endTime, $scenarioBox);
    });

    // Helper function to update scenario numbers after sorting or removal
    function updateScenarioNumbers() {
        $('.atlas-dmsg-scenario-box').each(function (index) {
            $(this).find('.scenario-number').text(index + 1);
        });
    }

    // Initialize tab switching to refresh previews
    $("#atlas-dmsg-tabs .nav-tab").on('click', function () {
        // If switching to Layout Settings tab
        if ($(this).attr('href') === '#layouts-settings') {
            // Small delay to ensure tab has loaded
            setTimeout(function () {
                initAllLayoutPreviews();
            }, 50);
        }
        // If switching to Scenarios tab
        else if ($(this).attr('href') === '#scenarios') {
            // Small delay to ensure tab has loaded
            setTimeout(function () {
                updateAllPreviews();
                // Apply translated day labels
                applyDayLabels();
            }, 50);
        }
    });

    // Initialize layout tabs switching to refresh previews
    $(".atlas-dmsg-layouts-tabs-nav li a").on('click', function () {
        // Small delay to ensure tab has loaded
        setTimeout(function () {
            initAllLayoutPreviews();
        }, 50);
    });

    // NEW FUNCTION: Initialize all layout previews on page load
    function initAllLayoutPreviews() {
        // For each layout tab, update its preview
        for (let layoutNum = 1; layoutNum <= 3; layoutNum++) {
            // Use the appropriate field as a trigger for the update based on layout type
            let fieldSelector;
            if (layoutNum === 3) {
                fieldSelector = `input[name="atlas_dmsg_settings[layouts][${layoutNum}][gradient_start]"]`;
            } else {
                fieldSelector = `input[name="atlas_dmsg_settings[layouts][${layoutNum}][bg_color]"]`;
            }

            var triggerField = $(fieldSelector);
            if (triggerField.length) {
                updateLayoutPreview(triggerField);
            }
        }

        // Update the preview text in all layout previews
        updateLayoutPreviewText();
    }

    // Function to update layout preview text with localized time
    function updateLayoutPreviewText() {
        // Get localized time text
        const localizedTimeText = getLocalizedTimePreview();

        // Update all layout preview text in the Layouts Settings tab
        $('.atlas-dmsg-live-preview').each(function () {
            const previewContent = $(this).find('.atlas-dmsg-preview-content');
            if (previewContent.length) {
                // Replace the default English text with localized version
                let content = atlasDmsgAdmin.strings.preview_text || 'Your customized message will appear like this. Time remaining: ';
                previewContent.html(content + ' ' + localizedTimeText);
            }
        });
    }

    // Initialize layout tabs switching to refresh previews
    $(".atlas-dmsg-layouts-tabs-nav li a").on('click', function () {
        // Small delay to ensure tab has loaded
        setTimeout(function () {
            initAllLayoutPreviews();
        }, 50);
    });

    // IMPORTANT: Initialize all previews on page load
    $(document).ready(function () {
        // Delayed initialization to ensure all elements are loaded
        setTimeout(function () {
            // Initialize all layout previews
            initAllLayoutPreviews();

            // Initialize all scenario previews
            updateAllPreviews();

            // Apply translated day labels
            applyDayLabels();
        }, 200);
    });
});