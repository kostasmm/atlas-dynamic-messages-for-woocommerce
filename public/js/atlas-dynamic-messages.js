/**
 * Atlas Dynamic Messages - Cache-Compatible Countdown System
 * 
 * This script implements a unique cache-bypassing countdown mechanism:
 * - Works with ALL caching plugins (WP Rocket, W3 Total Cache, Cloudflare, etc.)
 * - Fetches real-time data via REST API, bypassing page cache
 * - Updates countdown every second using client-side calculations
 * - Ensures accuracy even on heavily cached sites
 */
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('atlas-dmsg-countdown-container');
    const messageContainer = document.getElementById('atlas-dmsg-message-container');
    if (!container || !messageContainer) return;

    // Get current page type from container data attribute
    const currentPage = container.dataset.page;
    if (!currentPage) return;

    // Run theme-specific adjustments if defined
    if (window.atlasDmsgThemeCompat && window.atlasDmsgThemeCompat.isCustomized && 
        typeof window.atlasDmsgThemeCompat.adjustments === 'function') {
        window.atlasDmsgThemeCompat.adjustments();
    }

    // Add cache-busting parameter to bypass LiteSpeed/other server caches
    fetch(atlasDmsgVars.apiUrl + '?_=' + Date.now(), {
        headers: {
            'X-WP-Nonce': atlasDmsgVars.nonce
        }
    })
        .then(r => {
            if (!r.ok) {
                throw new Error(`Network error: ${r.status} ${r.statusText}`);
            }
            return r.json();
        })
        .then(data => {
            if (!data.scenarios || !Array.isArray(data.scenarios) || data.scenarios.length === 0) {
                container.style.display = 'none';
                return;
            }

            let serverMs = data.serverTime * 1000;
            let serverDate = new Date(serverMs);
            let currentDay = serverDate.getDay();
            let currentMins = serverDate.getHours() * 60 + serverDate.getMinutes();

            // Find all active scenarios based on day and time constraints
            const activeScenarios = data.scenarios.filter(scenario => {
                // Parse times
                let [sh, sm] = scenario.start_time.split(':').map(Number);
                let [eh, em] = scenario.end_time.split(':').map(Number);
                let startTotal = sh * 60 + sm;
                let endTotal = eh * 60 + em;
                
                // Check if time range crosses midnight
                const crossesMidnight = endTotal < startTotal;
                
                // Check if current time is within the active time range
                let isWithinTimeRange = false;
                
                if (crossesMidnight) {
                    // For midnight-crossing ranges, check if current time is either:
                    // - After the start time (but before midnight)
                    // - Before the end time (after midnight)
                    isWithinTimeRange = (currentMins >= startTotal || currentMins <= endTotal);
                } else {
                    // For normal ranges, check if current time is between start and end
                    isWithinTimeRange = (currentMins >= startTotal && currentMins <= endTotal);
                }
                
                // If not in the time range, scenario is not active
                if (!isWithinTimeRange) return false;
                
                // Check day validity - get previous and next days for midnight crossing
                const nextDay = (currentDay + 1) % 7;
                const previousDay = (currentDay + 6) % 7; // Same as currentDay - 1 but handles 0 correctly
                
                // Check if current day is valid
                let isDayValid = false;
                
                if (crossesMidnight) {
                    if (currentMins <= endTotal) {
                        // We're after midnight (in the second day)
                        // The scenario is valid ONLY if the PREVIOUS day (start day) is selected
                        isDayValid = scenario.days.includes(previousDay);
                    } else {
                        // We're before midnight (in the first day)
                        // The scenario is valid ONLY if the CURRENT day (start day) is selected
                        isDayValid = scenario.days.includes(currentDay);
                    }
                } else {
                    // Normal range, just check current day
                    isDayValid = scenario.days.includes(currentDay);
                }
                
                // If day is not valid, scenario is not active
                if (!isDayValid) return false;
                
                // Finally, check if this scenario should display on current page
                return scenario.display_location.includes(currentPage);
            });

            if (activeScenarios.length === 0) {
                container.style.display = 'none';
                return;
            }

            // Clear containers and make them visible
            messageContainer.innerHTML = '';
            messageContainer.style.display = 'block';
            container.style.display = 'block';

            // Store scenario elements in an array
            const activeScenarioElements = [];

            // Calculate deadline for each scenario
            activeScenarios.forEach((scenario, index) => {
                // Create scenario element with the layout settings
                const { wrapper: scenarioWrapper, content: scenarioContent } =
                    createScenarioElement(scenario, index, activeScenarios, data.layout_settings);
                messageContainer.appendChild(scenarioWrapper);
                
                // Parse end time
                let [endH, endM] = scenario.end_time.split(':').map(Number);
                
                // Check if time range crosses midnight
                let [startH, startM] = scenario.start_time.split(':').map(Number);
                let startTotalMins = startH * 60 + startM;
                let endTotalMins = endH * 60 + endM;
                const crossesMidnight = endTotalMins < startTotalMins;
                
                // Calculate deadline based on current time and whether range crosses midnight
                let deadline;
                
                if (crossesMidnight) {
                    if (currentMins <= endTotalMins) {
                        // We're after midnight but before the end time
                        // Deadline is today
                        deadline = new Date(serverDate.getFullYear(), serverDate.getMonth(),
                            serverDate.getDate(), endH, endM, 0).getTime();
                    } else if (currentMins >= startTotalMins) {
                        // We're after start time but before midnight
                        // Deadline is tomorrow
                        const tomorrow = new Date(serverDate);
                        tomorrow.setDate(tomorrow.getDate() + 1);
                        deadline = new Date(tomorrow.getFullYear(), tomorrow.getMonth(),
                            tomorrow.getDate(), endH, endM, 0).getTime();
                    }
                } else {
                    // Normal range within the same day
                    deadline = new Date(serverDate.getFullYear(), serverDate.getMonth(),
                        serverDate.getDate(), endH, endM, 0).getTime();
                }
                
                // Store all necessary information
                activeScenarioElements.push({
                    scenario: scenario,
                    element: scenarioContent,
                    deadline: deadline,
                    wrapper: scenarioWrapper
                });
                
                // Initial update of the countdown
                updateCountdown(scenario, scenarioContent, deadline, serverMs);
            });

            // One single interval for all scenarios
            const intervalId = setInterval(() => {
                serverMs += 1000;

                // Update all scenarios
                for (let i = activeScenarioElements.length - 1; i >= 0; i--) {
                    const item = activeScenarioElements[i];
                    const stillActive = updateCountdown(item.scenario, item.element, item.deadline, serverMs);

                    if (!stillActive) {
                        // Remove inactive scenario from DOM
                        item.wrapper.remove();
                        // Remove scenario from array
                        activeScenarioElements.splice(i, 1);
                    }
                }

                // If no active scenarios remain, clear the interval
                if (activeScenarioElements.length === 0) {
                    clearInterval(intervalId);
                    container.style.display = 'none';
                }
            }, 1000);
        })
        .catch(err => {
            // Only log errors in development/debug mode
            if (typeof atlasDmsgVars.debug !== 'undefined' && atlasDmsgVars.debug) {
                console.error("Atlas Dynamic Messages Error:", err.message);
            }
            container.style.display = 'none';

            // Add visual feedback for admin users
            if (typeof atlasDmsgVars.isAdmin !== 'undefined' && atlasDmsgVars.isAdmin) {
                container.innerHTML = `<div class="atlas-dmsg-error">Error loading messages: ${err.message}</div>`;
                container.style.display = 'block';
            }
        });

    /**
     * Creates a scenario element with appropriate styling based on layout settings
     * 
     * @param {Object} scenario The scenario object
     * @param {number} index The index of the scenario in the active scenarios array
     * @param {Array} activeScenarios Array of all active scenarios
     * @param {Object} layoutSettings Layout settings from the server
     * @returns {Object} Object containing wrapper and content elements
     */
    function createScenarioElement(scenario, index, activeScenarios, layoutSettings) {
        // Create the wrapper
        const scenarioWrapper = document.createElement('div');
        scenarioWrapper.className = `atlas-dmsg-wrapper atlas-dmsg-layout-${scenario.layout || 1}`;
        scenarioWrapper.style.marginBottom = (index < activeScenarios.length - 1) ? '20px' : '0';

        // Create the content container
        const scenarioContent = document.createElement('div');
        scenarioContent.className = 'atlas-dmsg-box';
        scenarioContent.setAttribute('role', 'alert');
        scenarioContent.setAttribute('aria-live', 'polite');
        scenarioContent.setAttribute('aria-atomic', 'true');
        scenarioContent.setAttribute('aria-relevant', 'all');
        scenarioContent.style.display = 'block';

        // Apply custom styles if available in layoutSettings
        applyLayoutStyles(scenarioContent, scenario.layout, layoutSettings);

        // Check if we need to make it clickable
        if (scenario.url && scenario.url.trim() !== '') {
            // Create link element
            const linkElement = document.createElement('a');
            linkElement.href = scenario.url;
            linkElement.className = 'atlas-dmsg-link';
            linkElement.setAttribute('role', 'link');
            linkElement.target = "_blank";
            linkElement.rel = "noopener noreferrer";

            // Add content to link and link to wrapper
            linkElement.appendChild(scenarioContent);
            scenarioWrapper.appendChild(linkElement);
        } else {
            // Just add content directly to wrapper
            scenarioWrapper.appendChild(scenarioContent);
        }

        return { wrapper: scenarioWrapper, content: scenarioContent };
    }

    /**
     * Applies layout styles to an element based on layout settings
     * 
     * @param {HTMLElement} element The element to apply styles to
     * @param {number} layoutId The layout ID (1, 2, or 3)
     * @param {Object} layoutSettings Layout settings from the server
     */
    function applyLayoutStyles(element, layoutId, layoutSettings) {
        if (!layoutSettings || !layoutSettings[layoutId]) return;
    
        const settings = layoutSettings[layoutId];
    
        // Apply common settings
        if (settings.bg_color) element.style.backgroundColor = settings.bg_color;
        if (settings.text_color) element.style.color = settings.text_color;
    
        // Apply border settings
        if (settings.border_color) {
            if (layoutId == 1) {
                const borderStyle = settings.border_style || 'dashed';
                element.style.borderLeft = `2px ${borderStyle} ${settings.border_color}`;
                element.style.borderRight = `2px ${borderStyle} ${settings.border_color}`;
                element.style.borderBottom = `2px ${borderStyle} ${settings.border_color}`;
                
                // Instead of creating a DOM element, add a class and set a custom property
                element.classList.add('has-accent-bar');
                element.style.setProperty('--accent-bar-color', settings.border_color);
            } else {
                element.style.border = `2px solid ${settings.border_color}`;
            }
        }
    
        // Layout-specific settings
        if (layoutId == 2) {
            // Modern Card Layout
            if (settings.border_radius) {
                element.style.borderRadius = `${settings.border_radius}px`;
            }
    
            if (settings.shadow_intensity) {
                let shadowStyle = '';
                switch (settings.shadow_intensity) {
                    case 'light': shadowStyle = '0 2px 4px rgba(0, 0, 0, 0.05)'; break;
                    case 'medium': shadowStyle = '0 4px 6px rgba(0, 0, 0, 0.1)'; break;
                    case 'strong': shadowStyle = '0 6px 10px rgba(0, 0, 0, 0.15)'; break;
                }
                element.style.boxShadow = shadowStyle;
            }
            
            // Add icon class instead of creating DOM element
            if (settings.icon) {
                element.classList.add('has-icon');
                element.style.setProperty('--icon-content', `"${settings.icon}"`);
            }
        } else if (layoutId == 3) {
            // Gradient Alert Layout
            if (settings.gradient_start && settings.gradient_end) {
                const direction = settings.gradient_direction || '135deg';
                element.style.background = `linear-gradient(${direction}, ${settings.gradient_start}, ${settings.gradient_end})`;
            }
    
            if (settings.accent_height && settings.accent_color) {
                // Use CSS custom properties instead of DOM elements
                element.classList.add('has-accent-bar');
                element.style.setProperty('--accent-bar-height', `${settings.accent_height}px`);
                element.style.setProperty('--accent-bar-color', settings.accent_color);
            }
            
            // Add icon using custom property
            if (settings.icon) {
                element.classList.add('has-icon');
                element.style.setProperty('--icon-content', `"${settings.icon}"`);
            }
        }
    }

    /**
     * Update the countdown display for a scenario
     * 
     * @param {Object} scenario The scenario object
     * @param {HTMLElement} element The DOM element to update
     * @param {number} deadline The timestamp when the scenario ends
     * @param {number} currentTime The current timestamp
     * @returns {boolean} Whether the scenario is still active
     */
    function updateCountdown(scenario, element, deadline, currentTime) {
        let diff = deadline - currentTime;

        if (diff <= 0) {
            return false; // The scenario is no longer active
        }

        let totalSecs = Math.floor(diff / 1000);
        let hours = Math.floor(totalSecs / 3600);
        totalSecs %= 3600;
        let mins = Math.floor(totalSecs / 60);
        let secs = totalSecs % 60;

        let remainText = '';

        if (hours > 0) {
            // Use the correct plural/singular form based on the number
            let hoursText = hours === 1 ? atlasDmsgVars.time.hours.one : atlasDmsgVars.time.hours.many;
            remainText += `${hours} ${hoursText}`;

            if (mins > 0 || secs > 0) {
                remainText += ` ${atlasDmsgVars.comma} `;
            }
        }

        if (mins > 0) {
            // Use the correct plural/singular form based on the number
            let minsText = mins === 1 ? atlasDmsgVars.time.minutes.one : atlasDmsgVars.time.minutes.many;
            remainText += `${mins} ${minsText}`;

            if (secs > 0) {
                remainText += ` ${atlasDmsgVars.and} `;
            }
        }

        if (secs > 0 || (hours === 0 && mins === 0)) {
            // Use the correct plural/singular form based on the number
            let secsText = secs === 1 ? atlasDmsgVars.time.seconds.one : atlasDmsgVars.time.seconds.many;
            remainText += `${secs} ${secsText}`;
        }

        element.innerHTML = scenario.message.replace('{time_remain}', `<strong>${remainText}</strong>`);
        return true; // The scenario is still active
    }
});