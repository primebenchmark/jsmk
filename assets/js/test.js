/* ============================================
   JFT Mock Test — Test Logic
   Section-by-section navigation + Scoring
   ============================================ */

// State
const userAnswers = {};
const audioInstances = {};
const audioPlayCounts = {};
let isSubmitted = false;
let currentSectionIndex = 0;

// ============================================
// INITIALIZATION
// ============================================

document.addEventListener('DOMContentLoaded', () => {
    loadSavedAnswers();
    initializeAudio();
    updateProgress();
    showCurrentSection();

});

// ============================================
// SECTION NAVIGATION (step-by-step)
// ============================================

function showCurrentSection() {
    const currentSection = SECTION_KEYS[currentSectionIndex];

    // Update tabs
    document.querySelectorAll('.section-tab').forEach(tab => {
        tab.classList.toggle('active', parseInt(tab.dataset.section) === currentSection);
    });

    // Show/hide questions
    document.querySelectorAll('.question-card').forEach(card => {
        if (parseInt(card.dataset.section) === currentSection) {
            card.style.display = 'block';
            card.classList.add('fade-in-up');
        } else {
            card.style.display = 'none';
        }
    });

    // Update navigation buttons
    updateSectionNav();

    // Lazy load the current section media
    loadSectionMedia(currentSection);

    // Scroll to top of container
    const container = document.querySelector('.test-container');
    if (container) {
        container.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

function navigateToSection(sectionNum) {
    if (isSubmitted) {
        // After submission, allow free navigation
        currentSectionIndex = SECTION_KEYS.indexOf(sectionNum);
        if (currentSectionIndex === -1) currentSectionIndex = 0;
        showCurrentSection();
        return;
    }

    const targetIndex = SECTION_KEYS.indexOf(sectionNum);
    if (targetIndex === -1) return;

    // Only allow going to completed or current sections
    if (targetIndex <= currentSectionIndex) {
        currentSectionIndex = targetIndex;
        showCurrentSection();
    }
}

function nextSection() {
    if (currentSectionIndex < SECTION_KEYS.length - 1) {
        // Validation check for current section
        const currentSection = SECTION_KEYS[currentSectionIndex];
        const sectionCards = document.querySelectorAll(`.question-card[data-section="${currentSection}"]`);
        let allAnswered = true;
        let unansweredCount = 0;
        let firstUnanswered = null;

        sectionCards.forEach(card => {
            const qId = parseInt(card.dataset.questionId);
            if (userAnswers[qId] === undefined) {
                allAnswered = false;
                unansweredCount++;
                if (!firstUnanswered) firstUnanswered = card;
            }
        });

        if (IS_COMPULSORY && !allAnswered) {
            if (typeof showModal === 'function') {
                showModal('Action Required', `Please answer all questions in this section before proceeding.\n${unansweredCount} question(s) remaining.`);
            } else {
                alert(`Please answer all questions in this section before proceeding.\n${unansweredCount} question(s) remaining.`);
            }
            if (firstUnanswered) firstUnanswered.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return; // Prevent moving to next section
        }

        // Mark current tab as completed
        const currentTab = document.getElementById(`sectionTab-${SECTION_KEYS[currentSectionIndex]}`);
        if (currentTab) currentTab.classList.add('completed');

        currentSectionIndex++;
        showCurrentSection();
    }
}

function prevSection() {
    if (currentSectionIndex > 0) {
        currentSectionIndex--;
        showCurrentSection();
    }
}

function updateSectionNav() {
    const prevBtn = document.getElementById('prevSectionBtn');
    const nextBtn = document.getElementById('nextSectionBtn');
    const submitBtn = document.getElementById('submitBtn');
    const sectionNav = document.getElementById('sectionNav');

    if (isSubmitted) {
        // After submission, show all section nav freely
        if (prevBtn) prevBtn.style.display = currentSectionIndex > 0 ? 'inline-flex' : 'none';
        if (nextBtn) nextBtn.style.display = currentSectionIndex < SECTION_KEYS.length - 1 ? 'inline-flex' : 'none';
        return;
    }

    if (prevBtn) {
        prevBtn.style.display = currentSectionIndex > 0 ? 'inline-flex' : 'none';
    }

    if (nextBtn) {
        if (currentSectionIndex < SECTION_KEYS.length - 1) {
            nextBtn.style.display = 'inline-flex';
            nextBtn.textContent = 'Next Section →';
        } else {
            nextBtn.style.display = 'none';
        }
    }

    // Show/hide submit button: only on last section
    const testActions = document.getElementById('testActions');
    if (testActions) {
        testActions.style.display = currentSectionIndex === SECTION_KEYS.length - 1 ? 'flex' : 'none';
    }
}

// ============================================
// ANSWER SELECTION
// ============================================

function selectAnswer(questionId, answerId) {
    if (isSubmitted) return;

    // Clear previous selection
    document.querySelectorAll(`[id^="answer-${questionId}-"]`).forEach(opt => {
        opt.classList.remove('selected');
    });

    // Select new
    const option = document.getElementById(`answer-${questionId}-${answerId}`);
    if (option) {
        option.classList.add('selected');
        option.querySelector('input').checked = true;
    }

    userAnswers[questionId] = answerId;
    saveAnswers();
    updateProgress();

    // Mark card as answered
    const card = document.getElementById(`question-${questionId}`);
    if (card) card.classList.add('answered');
}

// ============================================
// PROGRESS TRACKING
// ============================================

function updateProgress() {
    const total = TEST_DATA.length;
    const answered = Object.keys(userAnswers).length;
    const percent = total > 0 ? (answered / total) * 100 : 0;

    const bar = document.getElementById('progressBar');
    const text = document.getElementById('progressText');

    if (bar) bar.style.width = percent + '%';
    if (text) text.textContent = `${answered} / ${total}`;
}

// ============================================
// AUTO-SAVE (localStorage)
// ============================================

function saveAnswers() {
    try {
        localStorage.setItem(`mcq_answers_${TEST_ID}`, JSON.stringify(userAnswers));
    } catch (e) { }
}

function loadSavedAnswers() {
    try {
        const saved = localStorage.getItem(`mcq_answers_${TEST_ID}`);
        if (saved) {
            const parsed = JSON.parse(saved);
            Object.entries(parsed).forEach(([qId, aId]) => {
                selectAnswer(parseInt(qId), parseInt(aId));
            });
        }
    } catch (e) { }
}

// ============================================
// ASYNC MEDIA LOADING 
// ============================================

function loadSectionMedia(sectionId) {
    document.querySelectorAll(`.question-card[data-section="${sectionId}"]`).forEach(card => {
        // Load images
        card.querySelectorAll('img[data-src]').forEach(img => {
            // Check if not already loaded (img.src might be empty or same as site url)
            if (!img.dataset.loaded) {
                const id = img.id.replace('img-', '');
                img.onload = function () { imageLoaded(this); };
                img.onerror = function () { imageError(this); };
                img.src = img.dataset.src;
                img.dataset.loaded = "true";
            }
        });

        // Load audio
        card.querySelectorAll('audio[data-src]').forEach(audioEl => {
            if (!audioEl.dataset.loaded) {
                const id = audioEl.id.replace('audio-', '');
                loadAudioWithRetry(audioEl, id);
                audioEl.dataset.loaded = "true";
            }
        });
    });
}

// ============================================
// AUDIO PLAYER
// ============================================

function initializeAudio() {
    document.querySelectorAll('audio[data-src]').forEach(audioEl => {
        const id = audioEl.id.replace('audio-', '');
        audioPlayCounts[id] = 0;
    });
}

function loadAudioWithRetry(audioEl, id) {
    const src = audioEl.dataset.src;
    let retries = parseInt(audioEl.dataset.retries) || 0;
    const maxRetries = parseInt(audioEl.dataset.maxRetries) || 5;

    audioEl.src = src + '?t=' + Date.now();

    audioEl.addEventListener('canplaythrough', function handler() {
        audioEl.removeEventListener('canplaythrough', handler);
        audioInstances[id] = audioEl;
    }, { once: true });

    audioEl.addEventListener('error', function handler() {
        audioEl.removeEventListener('error', handler);
        retries++;
        audioEl.dataset.retries = retries;

        if (retries <= maxRetries) {
            const delay = Math.min(1000 * Math.pow(2, retries - 1), 10000);
            console.log(`Audio ${id}: retry ${retries}/${maxRetries} in ${delay}ms`);
            setTimeout(() => loadAudioWithRetry(audioEl, id), delay);
        } else {
            const player = document.getElementById(`audio-player-${id}`);
            if (player) {
                player.innerHTML = `
                    <div class="audio-loading">
                        <span>⚠️ Failed to load audio</span>
                        <button class="image-retry-btn" onclick="retryAudioManual(${id})">Retry</button>
                    </div>
                `;
            }
        }
    }, { once: true });

    audioEl.load();
}

function retryAudioManual(id) {
    const audioEl = document.getElementById(`audio-${id}`);
    if (audioEl) {
        audioEl.dataset.retries = '0';
        loadAudioWithRetry(audioEl, id);
    }
}

function toggleAudio(questionId) {
    const audioEl = document.getElementById(`audio-${questionId}`);
    const btn = document.getElementById(`audio-btn-${questionId}`);
    if (!audioEl || !btn) return;

    const maxPlays = parseInt(audioEl.dataset.maxPlays) || AUDIO_LIMIT;
    let playCount = parseInt(audioEl.dataset.plays) || 0;

    // If currently playing, pause
    if (btn.dataset.playing === 'true') {
        audioEl.pause();
        btn.dataset.playing = 'false';
        btn.textContent = '▶';
        return;
    }

    // Check play limit
    if (playCount >= maxPlays) {
        return;
    }

    // Stop any other playing audio
    Object.keys(audioInstances).forEach(key => {
        if (key != questionId) {
            const otherAudio = audioInstances[key];
            const otherBtn = document.getElementById(`audio-btn-${key}`);
            if (otherAudio && !otherAudio.paused) {
                otherAudio.pause();
                if (otherBtn) {
                    otherBtn.dataset.playing = 'false';
                    otherBtn.textContent = '▶';
                }
            }
        }
    });

    // Play
    audioEl.currentTime = 0;
    audioEl.play().then(() => {
        btn.dataset.playing = 'true';
        btn.textContent = '⏸';

        // Increment play count
        playCount++;
        audioEl.dataset.plays = playCount;
        updatePlayCount(questionId, playCount, maxPlays);

        // Progress tracking
        const progressFill = document.getElementById(`audio-progress-${questionId}`);
        const timeDisplay = document.getElementById(`audio-time-${questionId}`);

        const updateInterval = setInterval(() => {
            if (audioEl.paused || audioEl.ended) {
                clearInterval(updateInterval);
                return;
            }
            const progress = (audioEl.currentTime / audioEl.duration) * 100;
            if (progressFill) progressFill.style.width = progress + '%';
            if (timeDisplay) {
                timeDisplay.textContent = `${formatTime(audioEl.currentTime)} / ${formatTime(audioEl.duration)}`;
            }
        }, 100);

        audioEl.onended = () => {
            btn.dataset.playing = 'false';
            btn.textContent = '▶';
            if (progressFill) progressFill.style.width = '100%';

            // Disable if limit reached
            if (playCount >= maxPlays) {
                btn.disabled = true;
            }
        };
    }).catch(err => {
        console.error('Audio play failed:', err);
    });
}

function updatePlayCount(questionId, current, max) {
    const el = document.getElementById(`plays-left-${questionId}`);
    if (!el) return;

    const remaining = max - current;
    el.textContent = `${remaining} plays remaining`;

    el.classList.remove('warning', 'depleted');
    if (remaining === 0) {
        el.classList.add('depleted');
        el.textContent = 'No plays remaining';
    } else if (remaining === 1) {
        el.classList.add('warning');
    }
}

function formatTime(seconds) {
    if (isNaN(seconds)) return '0:00';
    const m = Math.floor(seconds / 60);
    const s = Math.floor(seconds % 60);
    return `${m}:${s.toString().padStart(2, '0')}`;
}

// ============================================
// IMAGE RETRY LOGIC
// ============================================

function imageLoaded(img) {
    img.style.display = 'block';
    const id = img.id.replace('img-', '');
    const loading = document.getElementById(`img-loading-${id}`);
    const error = document.getElementById(`img-error-${id}`);
    if (loading) loading.style.display = 'none';
    if (error) error.style.display = 'none';
}

function imageError(img) {
    let retries = parseInt(img.dataset.retries) || 0;
    const maxRetries = parseInt(img.dataset.maxRetries) || 5;
    const id = img.id.replace('img-', '');

    retries++;
    img.dataset.retries = retries;

    if (retries <= maxRetries) {
        const delay = Math.min(1000 * Math.pow(2, retries - 1), 10000);
        console.log(`Image ${id}: retry ${retries}/${maxRetries} in ${delay}ms`);
        setTimeout(() => {
            img.src = img.dataset.src + '?t=' + Date.now();
        }, delay);
    } else {
        const loading = document.getElementById(`img-loading-${id}`);
        const error = document.getElementById(`img-error-${id}`);
        if (loading) loading.style.display = 'none';
        if (error) error.style.display = 'flex';
    }
}

function retryImage(questionId) {
    const img = document.getElementById(`img-${questionId}`);
    if (!img) return;

    const loading = document.getElementById(`img-loading-${questionId}`);
    const error = document.getElementById(`img-error-${questionId}`);
    if (loading) loading.style.display = 'flex';
    if (error) error.style.display = 'none';

    img.dataset.retries = '0';
    img.src = img.dataset.src + '?t=' + Date.now();
}

// ============================================
// SUBMIT & SCORING
// ============================================

function submitTest() {
    const total = TEST_DATA.length;
    const answered = Object.keys(userAnswers).length;

    // Collect device info instead of asking for a name
    const deviceInfo = [
        navigator.platform || 'Unknown Platform',
        navigator.userAgent.split(' ').pop(),
        Intl.DateTimeFormat().resolvedOptions().timeZone || 'Unknown TZ',
        `${screen.width}x${screen.height}`,
        navigator.language || 'Unknown'
    ].join(' | ');

    if (IS_COMPULSORY && answered < total) {
        if (typeof showModal === 'function') {
            showModal('Action Required', `You must answer all questions to submit.\n${total - answered} question(s) remaining.`);
        } else {
            alert(`You must answer all questions to submit.\n${total - answered} question(s) remaining.`);
        }
        return;
    }

    isSubmitted = true;

    let correct = 0;
    let incorrect = 0;
    let unanswered = 0;
    let totalMarks = 0;
    let earnedMarks = 0;

    TEST_DATA.forEach(question => {
        const marksForQuestion = question.marks !== undefined ? parseFloat(question.marks) : 1;
        totalMarks += marksForQuestion;

        const qId = question.id;
        const correctId = question.keyid;
        const userAnswer = userAnswers[qId];

        // Show feedback
        const feedback = document.getElementById(`feedback-${qId}`);
        if (feedback) feedback.classList.add('visible');

        if (userAnswer === undefined) {
            unanswered++;
            // Highlight correct answer
            const correctOpt = document.getElementById(`answer-${qId}-${correctId}`);
            if (correctOpt) correctOpt.classList.add('correct');
        } else if (userAnswer === correctId) {
            correct++;
            earnedMarks += marksForQuestion;
            const selectedOpt = document.getElementById(`answer-${qId}-${userAnswer}`);
            if (selectedOpt) {
                selectedOpt.classList.remove('selected');
                selectedOpt.classList.add('correct');
            }
        } else {
            incorrect++;
            const selectedOpt = document.getElementById(`answer-${qId}-${userAnswer}`);
            if (selectedOpt) {
                selectedOpt.classList.remove('selected');
                selectedOpt.classList.add('incorrect');
            }
            const correctOpt = document.getElementById(`answer-${qId}-${correctId}`);
            if (correctOpt) correctOpt.classList.add('correct');
        }
    });

    const percent = totalMarks > 0 ? Math.round((earnedMarks / totalMarks) * 100) : 0;

    // Show results panel
    document.getElementById('scoreDisplay').textContent = percent + '%';
    document.getElementById('marksDisplay').textContent = `${earnedMarks} / ${totalMarks} marks`;
    document.getElementById('scoreSubtitle').textContent =
        earnedMarks >= PASS_MARK ? 'Congratulations! You passed! 🎉' :
            'Keep studying! You missed the pass mark. 📚';
    document.getElementById('correctCount').textContent = correct;
    document.getElementById('incorrectCount').textContent = incorrect;
    document.getElementById('unansweredCount').textContent = unanswered;

    const resultsPanel = document.getElementById('resultsPanel');
    resultsPanel.classList.add('visible');

    // Show score summary at top
    const scoreSummary = document.getElementById('scoreSummary');
    if (scoreSummary) {
        document.getElementById('scoreSummaryText').textContent = `${earnedMarks} / ${totalMarks}`;
        document.getElementById('scoreSummaryDetail').textContent = `marks (${percent}%)`;
        scoreSummary.classList.add('visible');
    }

    // Change submit button
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.textContent = '✅ Graded';
    submitBtn.disabled = true;

    // Disable answer selection styling
    document.querySelectorAll('.answer-option').forEach(opt => {
        opt.style.cursor = 'default';
    });

    // Show ALL sections and make tabs freely navigable
    document.querySelectorAll('.question-card').forEach(card => {
        card.style.display = 'none';
    });

    // Go to section 1 (first section) after submission
    currentSectionIndex = 0;
    showCurrentSection();

    // Make all tabs clickable after submission
    document.querySelectorAll('.section-tab').forEach(tab => {
        tab.classList.remove('completed');
    });

    // Show test actions on all sections
    const testActions = document.getElementById('testActions');
    if (testActions) testActions.style.display = 'flex';

    // Scroll to question 1
    const firstQuestion = document.getElementById('question-1');
    if (firstQuestion) {
        setTimeout(() => {
            firstQuestion.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 300);
    }

    // Clear saved answers
    try {
        localStorage.removeItem(`mcq_answers_${TEST_ID}`);
    } catch (e) { }

    // Save analytics
    fetch('save_analytics.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            test_id: TEST_ID,
            device_info: deviceInfo,
            score_percent: percent,
            correct_count: correct,
            total_questions: total
        })
    }).catch(err => console.error('Failed to save analytics', err));
}
