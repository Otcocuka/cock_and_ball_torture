<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Custom Pomodoro Timer</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <p id="work" class="absolute text-[150px]">Работай, сука</p>
    <p id="rest" class="absolute text-[180px]">Отдыхай, блядина</p>
    
    <div class="bg-white p-8 rounded-lg shadow-lg w-96 relative z-2">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">🍅 Pomodoro Timer</h1>
            <div id="timer" class="text-4xl font-mono my-4">25:00</div>
    
            <div class="space-y-4">
                <div class="flex flex-col">
                    <label class="text-sm text-gray-600 mb-1">Work (minutes)</label>
                    <input type="number" step="0.1" id="workTime" class="p-2 border rounded" min="1" value="25">
                </div>
                <div class="flex flex-col">
                    <label class="text-sm text-gray-600 mb-1">Short Break (minutes)</label>
                    <input type="number" step="0.1" id="shortBreak" class="p-2 border rounded" min="1" value="5">
                </div>
                <div class="flex flex-col">
                    <label class="text-sm text-gray-600 mb-1">Long Break (minutes)(every 4th time)</label>
                    <input type="number" id="longBreak" class="p-2 border rounded" min="1" value="15">
                </div>
                <!-- Новый инпут для задания, через сколько циклов должен срабатывать длинный перерыв -->
                <div class="flex flex-col">
                    <label class="text-sm text-gray-600 mb-1">Длинный перерыв каждый n-й цикл</label>
                    <input type="number" id="longCycle" class="p-2 border rounded" min="1" value="4">
                </div>
            
            </div>
    
            <div class="mt-6 flex justify-center space-x-2 flex-wrap">
                <button id="startBtn" class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
                    Start
                </button>
                <button id="pauseBtn" disabled class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600">
                    Pause
                </button>
                <button id="resetBtn" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                    Reset
                </button>
                <button id="resetStatsBtn" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 mt-2">
                    Reset Stats
                </button>
            </div>
    
    
    
            <!-- Добавить после кнопок -->
            <div class="mt-6 text-sm text-gray-600 space-y-1">
                <div>Total Sessions: <span id="totalSessions">0</span></div>
                <div>Total Work: <span id="totalWork">0</span></div>
                <div>Total Break: <span id="totalBreak">0</span></div>
                <div>Total Paused: <span id="totalPaused">0</span></div>
            </div>
        </div>
    </div>

    <script>

            // В основном коде приложения
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register('/sw.js')
                    .then(reg => {
                    console.log('SW registered:', reg);
                    reg.addEventListener('updatefound', () => {
                        console.log('New SW found!');
                    });
                    });
                
                navigator.serviceWorker.addEventListener('controllerchange', () => {
                    console.log('Controller changed - reloading');
                    window.location.reload();
                });
            }


            class PomodoroTimer {
                constructor() {
                // Объект пользователя
                this.user = {
                    settings: {
                        work: 25,
                        short: 5,
                        long: 15,
                        longCycle: 4 // Добавляем в дефолтные настройки
                    },
                    stats: {
                        totalSessions: 0,
                        totalWork: 0,
                        totalBreak: 0,
                        totalPaused: 0,
                        lastPauseTime: null
                    }
                };

                this.isWorking = true;
                this.isRunning = false;
                this.timeLeft = 0; // в секундах
                this.interval = null;
                this.cycles = 0;
                this.endTime = 0; // метка времени окончания текущей сессии (в мс)
                this.remainingPauseTime = 0; // оставшееся время в мс при паузе
                 // Добавляем новые свойства
                this.lastSaveTime = Date.now();
                this.SAVE_INTERVAL = 30000; // 30 секунд

                // DOM элементы
                this.timerDisplay = document.getElementById('timer');
                this.startBtn = document.getElementById('startBtn');
                this.pauseBtn = document.getElementById('pauseBtn');
                this.resetBtn = document.getElementById('resetBtn');
                this.workInput = document.getElementById('workTime');
                this.shortBreakInput = document.getElementById('shortBreak');
                this.longBreakInput = document.getElementById('longBreak');
                this.longCycleInput = document.getElementById('longCycle');
                this.resetStatsBtn = document.getElementById('resetStatsBtn');
                
                // Элементы статистики
                this.totalSessions = document.getElementById('totalSessions');
                this.totalWork = document.getElementById('totalWork');
                this.totalBreak = document.getElementById('totalBreak');
                this.totalPaused = document.getElementById('totalPaused');

                // Загрузка сохраненных данных
                this.loadData();
                this.initTimer();
                this.updateDisplay();
                this.updateStatsDisplay();

                // Обработчики событий
                this.addEventListeners();
            }

            // Инициализация таймера
            initTimer() {
                this.timeLeft = this.user.settings.work * 60;
                this.isWorking = true;
                this.endTime = Date.now() + this.timeLeft * 1000;
                this.updateDisplay();
            }

            // Загрузка данных из localStorage
            loadData() {
                const savedSettings = localStorage.getItem('pomodoroSettings');
                const savedStats = localStorage.getItem('pomodoroStats');
                if (savedSettings) {
                    this.user.settings = JSON.parse(savedSettings);
                    this.workInput.value = this.user.settings.work;
                    this.shortBreakInput.value = this.user.settings.short;
                    this.longBreakInput.value = this.user.settings.long;
                    this.longCycleInput.value = this.user.settings.longCycle || 4;
                }
                if (savedStats) {
                    this.user.stats = JSON.parse(savedStats);
                    this.user.stats.lastPauseTime = null;
                }
            }

            // Сохранение данных
            saveData() {
                const statsToSave = { 
                    ...this.user.stats,
                    // Рассчитываем актуальное время работы/перерыва
                    totalWork: this.calculateAccurateWorkTime(),
                    totalBreak: this.calculateAccurateBreakTime()
                };
                
                localStorage.setItem('pomodoroSettings', JSON.stringify(this.user.settings));
                localStorage.setItem('pomodoroStats', JSON.stringify(statsToSave));
            }


            // Обновление настроек
            updateSettings(type, value) {
                if (type === 'longCycle') {
                    const parsed = Math.max(1, parseInt(value) || 1);
                    // Сбрасываем счетчик циклов при изменении настройки
                    if (this.user.settings.longCycle !== parsed) {
                        this.cycles = 0;
                    }
                    this.user.settings[type] = parsed;
                    return;
                }

                const normalizedValue = value.replace(',', '.');
                const parsed = Math.max(0.1, parseFloat(normalizedValue) || 0.1);
                this.user.settings[type] = parsed;

                if (!this.isRunning) {
                    this.reset();
                }
            }


            // Обновление статистики
            updateStats(type, value) {
                this.user.stats[type] += value;
                
                // Сохраняем только если прошло больше 30 секунд
                if (Date.now() - this.lastSaveTime > this.SAVE_INTERVAL) {
                    this.saveData();
                    this.lastSaveTime = Date.now();
                }
                
                this.updateStatsDisplay();
            }

            // Новые методы для точного расчета времени
            calculateAccurateWorkTime() {
                if (!this.isRunning || !this.isWorking) return this.user.stats.totalWork;
                const extraSeconds = Math.floor((Date.now() - this.endTime) / 1000);
                return this.user.stats.totalWork + extraSeconds;
            }

            calculateAccurateBreakTime() {
                if (!this.isRunning || this.isWorking) return this.user.stats.totalBreak;
                const extraSeconds = Math.floor((Date.now() - this.endTime) / 1000);
                return this.user.stats.totalBreak + extraSeconds;
            }

            // Отображение статистики
            updateStatsDisplay() {
                this.totalSessions.textContent = this.user.stats.totalSessions;
                this.totalWork.textContent = this.formatDuration(this.user.stats.totalWork);
                this.totalBreak.textContent = this.formatDuration(this.user.stats.totalBreak);
                this.totalPaused.textContent = this.formatDuration(this.user.stats.totalPaused);
            }

            // форматирование этой статистики
            formatDuration(totalSeconds) {
                if (totalSeconds === 0) return '0 сек';

                const hours = Math.floor(totalSeconds / 3600);
                const minutes = Math.floor((totalSeconds % 3600) / 60);
                const seconds = totalSeconds % 60;

                const parts = [];
                if (hours > 0) parts.push(`${hours} ч`);
                if (minutes > 0) parts.push(`${minutes} мин`);
                if (seconds > 0) parts.push(`${seconds} сек`);

                // Если остались только нулевые минуты/часы, но есть секунды
                if (parts.length === 0) return `${seconds} сек`;

                return parts.join(' ');
            }

            // Обработчики событий
            addEventListeners() {
                this.startBtn.addEventListener('click', () => this.start());
                this.pauseBtn.addEventListener('click', () => this.pause());
                this.resetBtn.addEventListener('click', () => this.reset());
                this.resetStatsBtn.addEventListener('click', () => this.resetStats());
                this.workInput.addEventListener('input', (e) => this.updateSettings('work', e.target.value));
                this.shortBreakInput.addEventListener('input', (e) => this.updateSettings('short', e.target.value));
                this.longBreakInput.addEventListener('input', (e) => this.updateSettings('long', e.target.value));
                this.longCycleInput = document.getElementById('longCycle');
                this.longCycleInput.addEventListener('input', (e) =>
                    this.updateSettings('longCycle', e.target.value)
                );

            }

            // Запуск таймера
            start() {
                if (!this.isRunning) {
                    this.isRunning = true;
                    // При возобновлении учитываем оставшееся время, если оно есть
                    if (this.remainingPauseTime) {
                        this.endTime = Date.now() + this.remainingPauseTime;
                        this.remainingPauseTime = 0;
                    } else {
                        this.endTime = Date.now() + this.timeLeft * 1000;
                    }
                    this.interval = setInterval(() => this.tick(), 1000);
                    this.startBtn.disabled = true;
                    this.pauseBtn.disabled = false;
                    this.user.stats.totalSessions++;
                    this.saveData();
                    this.updateStatsDisplay();
                }
            }
            // Пауза
            pause() {
                if (this.isRunning) {
                    this.isRunning = false;
                    this.user.stats.lastPauseTime = Date.now();
                    clearInterval(this.interval);
                    // Сохраняем оставшееся время в мс для возобновления
                    this.remainingPauseTime = this.endTime - Date.now();
                    this.pauseBtn.textContent = "Resume";
                    this.startBtn.disabled = false;
                } else {
                    const pausedDuration = Math.floor((Date.now() - this.user.stats.lastPauseTime) / 1000);
                    this.updateStats('totalPaused', pausedDuration);
                    this.isRunning = true;
                    this.endTime = Date.now() + this.remainingPauseTime;
                    this.interval = setInterval(() => this.tick(), 1000);
                    this.pauseBtn.textContent = "Pause";
                    this.startBtn.disabled = true;
                    this.remainingPauseTime = 0;
                }
                this.pauseBtn.disabled = false;
                this.saveData(); 
            }



            // Сброс
            reset() {
                clearInterval(this.interval);
                this.isRunning = false;
                this.cycles = 0; // Важно сбрасывать счетчик
                this.startBtn.disabled = false;
                this.pauseBtn.disabled = true;
                this.initTimer();
                this.updateDisplay();
                this.saveData(); 
            }
            // Новый метод сброса статистики
            resetStats() {
                // Сброс настроек
                this.user.settings = {
                    work: 25,
                    short: 5,
                    long: 15,
                    longCycle: 4   // добавляем сброс longCycle
                };

                // Сброс статистики
                this.user.stats = {
                    totalSessions: 0,
                    totalWork: 0,
                    totalBreak: 0,
                    totalPaused: 0,
                    lastPauseTime: null
                };

                // Обновление UI
                this.workInput.value = this.user.settings.work;
                this.shortBreakInput.value = this.user.settings.short;
                this.longBreakInput.value = this.user.settings.long;
                this.longCycleInput.value = this.user.settings.longCycle;

                this.saveData();
                this.updateStatsDisplay();
                this.reset();
            }

            // Тик таймера
            tick() {
                // Пересчитываем оставшееся время на основе endTime
                let diff = Math.round((this.endTime - Date.now()) / 1000);
                if (diff < 0) diff = 0;
                this.timeLeft = diff;
                // Обновляем статистику (по 1 секунде за тик)
                if (this.isWorking) {
                    this.updateStats('totalWork', 1);
                } else {
                    this.updateStats('totalBreak', 1);
                }
                if (this.timeLeft <= 0) {
                    this.nextPhase();
                    return;
                }
                this.updateDisplay();
            }

            // Переход к следующей фазе
            nextPhase() {
                this.playSound();
                if (this.isWorking) {
                    this.cycles++;
                    this.isWorking = false;
                    const shouldLongBreak = (this.cycles % this.user.settings.longCycle === 0);
                    this.timeLeft = shouldLongBreak
                        ? this.user.settings.long * 60
                        : this.user.settings.short * 60;
                    this.sendNotification("Break Time!", "Your work session has ended.");
                } else {
                    this.isWorking = true;
                    this.timeLeft = this.user.settings.work * 60;
                    this.sendNotification("Work Time!", "Your break has ended.");
                }
                this.endTime = Date.now() + this.timeLeft * 1000;
                this.updateDisplay();
                this.saveData(); 
            }


            sendNotification(title, body) {
                if (Notification.permission === 'granted' && navigator.serviceWorker) {
                    navigator.serviceWorker.ready.then(registration => {
                        registration.showNotification(title, {
                            body: body,
                            icon: 'icon.png', // замените на путь к вашей иконке, если требуется
                            vibrate: [100, 50, 100],
                            tag: 'pomodoro-notification'
                        });
                    });
                }
            }

            // Обновление отображения таймера
            updateDisplay() {
                const totalSeconds = this.timeLeft;
                const minutes = Math.floor(totalSeconds / 60);
                const seconds = Math.floor(totalSeconds % 60).toString().padStart(2, '0');
                // Добавляем отображение десятых долей, если время меньше минуты
                const tenths = totalSeconds < 60 ? Math.floor((totalSeconds % 1) * 10) : 0;
                this.timerDisplay.textContent = `${minutes}:${seconds}${totalSeconds < 60 ? `.${tenths}` : ''}`;

                document.body.className = this.isWorking
                    ? 'bg-red-100 min-h-screen flex items-center justify-center'
                    : 'bg-green-100 min-h-screen flex items-center justify-center';


                    document.querySelector('#work').className = this.isWorking
                    ? 'block absolute text-[180px]'
                    : 'hidden';

                    document.querySelector('#rest').className = this.isWorking
                    ? 'hidden'
                    : 'block absolute text-[180px]';
            }



          

            playSound() {
                // TODO: сделать возможность выбора звука
                // Настройки для разработчика (можно менять эти значения)
                const REPEAT_COUNT = 100;      // Количество проигрываний
                const REPEAT_INTERVAL = 20; // Интервал между повторами в миллисекундах

                let playCounter = 0;

                const playLoop = () => {
                    if (playCounter >= REPEAT_COUNT) return;

                    const audio = new Audio('data:audio/wav;base64,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qJrx4KOQjahZxWbcZgztj2c49nKmkId44S71j0c8eV9yDK6uPRzx5X18eDvjvQ6yKo9ZSS6l//8elePK/Lf//IInrOF/FvDoADYAGBMGb7FtErm5MXMlmPAJQVgWta7Zx2go+8xJ0UiCb8LHHdftWyLJE0QIAIsI+UbXu67dZMjmgDGCGl1H+vpF4NSDckSIkk7Vd+sxEhBQMRU8j/12UIRhzSaUdQ+rQU5kGeFxm+hb1oh6pWWmv3uvmReDl0UnvtapVaIzo1jZbf/pD6ElLqSX+rUmOQNpJFa/r+sa4e/pBlAABoAAAAA3CUgShLdGIxsY7AUABPRrgCABdDuQ5GC7DqPQCgbbJUAoRSUj+NIEig0YfyWUho1VBBBA//uQZB4ABZx5zfMakeAAAAmwAAAAF5F3P0w9GtAAACfAAAAAwLhMDmAYWMgVEG1U0FIGCBgXBXAtfMH10000EEEEEECUBYln03TTTdNBDZopopYvrTTdNa325mImNg3TTPV9q3pmY0xoO6bv3r00y+IDGid/9aaaZTGMuj9mpu9Mpio1dXrr5HERTZSmqU36A3CumzN/9Robv/Xx4v9ijkSRSNLQhAWumap82WRSBUqXStV/YcS+XVLnSS+WLDroqArFkMEsAS+eWmrUzrO0oEmE40RlMZ5+ODIkAyKAGUwZ3mVKmcamcJnMW26MRPgUw6j+LkhyHGVGYjSUUKNpuJUQoOIAyDvEyG8S5yfK6dhZc0Tx1KI/gviKL6qvvFs1+bWtaz58uUNnryq6kt5RzOCkPWlVqVX2a/EEBUdU1KrXLf40GoiiFXK///qpoiDXrOgqDR38JB0bw7SoL+ZB9o1RCkQjQ2CBYZKd/+VJxZRRZlqSkKiws0WFxUyCwsKiMy7hUVFhIaCrNQsKkTIsLivwKKigsj8XYlwt/WKi2N4d//uQRCSAAjURNIHpMZBGYiaQPSYyAAABLAAAAAAAACWAAAAApUF/Mg+0aohSIRobBAsMlO//Kk4soosy1JSFRYWaLC4qZBYWFRGZdwqKiwkNBVmoWFSJkWFxX4FFRQWR+LsS4W/rFRb/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////VEFHAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAU291bmRib3kuZGUAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAMjAwNGh0dHA6Ly93d3cuc291bmRib3kuZGUAAAAAAAAAACU=');
                    audio.play();

                    playCounter++;
                    if (playCounter < REPEAT_COUNT) {
                        setTimeout(playLoop, REPEAT_INTERVAL);
                    }
                };

                playLoop();
            }

        }

        // Initialize timer when page loads
        window.addEventListener('DOMContentLoaded', () => {
            new PomodoroTimer();
        });
    </script>
</body>
</html>