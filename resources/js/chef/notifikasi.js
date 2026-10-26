const configElement = document.getElementById('chefDashboardConfig');

if (configElement) {
    const ordersTableBody = document.getElementById('ordersTableBody');
    const ordersTableWrapper = document.getElementById('ordersTableWrapper');
    const ordersEmptyState = document.getElementById('ordersEmptyState');
    const orderCount = document.getElementById('orderCount');
    const soundStatus = document.getElementById('soundStatus');
    const audioWarning = document.getElementById('audioWarning');
    const pollUrl = configElement.dataset.pollUrl;
    const alarmAudioUrl = configElement.dataset.alarmAudioUrl;
    const currentStatus = configElement.dataset.currentStatus;

    let knownConfirmedOrderIds;
    try {
        knownConfirmedOrderIds = JSON.parse(configElement.dataset.knownConfirmedOrderIds || '[]');
    } catch (error) {
        console.error('Konfigurasi ID pesanan koki tidak valid:', error);
        knownConfirmedOrderIds = null;
    }

    if (ordersTableBody && ordersTableWrapper && ordersEmptyState && orderCount && soundStatus && audioWarning &&
        pollUrl && alarmAudioUrl && currentStatus && Array.isArray(knownConfirmedOrderIds)) {
        const alarmAudio = new Audio(alarmAudioUrl);
        alarmAudio.preload = 'auto';
        alarmAudio.volume = 0.8;
        alarmAudio.load();

        let alarmInterval = null;
        let pollInterval = null;
        let isCheckingOrders = false;
        let alarmArmed = false;
        let alarmPlaybackPending = false;
        let audioBlockedUntilGesture = false;
        let audioUnavailable = false;
        let pageActive = true;
        const confirmedOrderIds = new Set(knownConfirmedOrderIds.map(Number).filter((id) => Number.isInteger(id) && id > 0));
        const displayedOrderIds = new Set();

        ordersTableBody.querySelectorAll('[data-order-id]').forEach((row) => {
            const id = Number(row.dataset.orderId);
            if (Number.isInteger(id) && id > 0) displayedOrderIds.add(id);
        });

        function showAudioWarning(message) {
            audioWarning.textContent = message;
            audioWarning.classList.remove('hidden');
            soundStatus.textContent = 'Notifikasi suara belum dapat diputar';
        }

        function clearAudioWarning() {
            audioWarning.textContent = '';
            audioWarning.classList.add('hidden');
            soundStatus.textContent = 'Notifikasi suara siap';
        }

        function addOrderRow(order) {
            if (currentStatus !== 'confirmed' || !order.html) return;
            const template = document.createElement('template');
            template.innerHTML = order.html.trim();
            const row = template.content.firstElementChild;
            if (!row || !row.matches('[data-order-id]')) return;

            const id = Number(row.dataset.orderId);
            if (!Number.isInteger(id) || id < 1 || displayedOrderIds.has(id)) return;
            displayedOrderIds.add(id);
            ordersTableBody.prepend(row);
            ordersTableWrapper.classList.remove('hidden');
            ordersEmptyState.classList.add('hidden');
            orderCount.textContent = `${ordersTableBody.querySelectorAll('[data-order-id]').length} pesanan`;
        }

        function stopAlarmCycle() {
            if (alarmInterval !== null) {
                window.clearInterval(alarmInterval);
                alarmInterval = null;
            }
            alarmPlaybackPending = false;
            alarmAudio.pause();
            try {
                alarmAudio.currentTime = 0;
            } catch (error) {
                // Some browsers reject seeking before audio metadata is available.
            }
        }

        function reportPlaybackFailure(error) {
            audioBlockedUntilGesture = error?.name === 'NotAllowedError' || error?.name === 'SecurityError';
            if (audioBlockedUntilGesture) {
                showAudioWarning(
                    'Browser memblokir autoplay. Klik atau tekan tombol pada halaman untuk mengizinkan suara.'
                );
            } else {
                audioUnavailable = true;
                showAudioWarning('File alarm lokal gagal diputar. Periksa aset audio atau dukungan format browser.');
                console.error('Gagal memutar audio alarm:', error);
            }
        }

        function playAlarmOnce() {
            if (!pageActive || !alarmArmed || alarmPlaybackPending) return;
            alarmPlaybackPending = true;

            let playback;
            try {
                alarmAudio.currentTime = 0;
                playback = alarmAudio.play();
            } catch (error) {
                alarmPlaybackPending = false;
                reportPlaybackFailure(error);
                stopAlarmCycle();
                return;
            }

            Promise.resolve(playback).then(() => {
                alarmPlaybackPending = false;
                audioBlockedUntilGesture = false;
                clearAudioWarning();
                if (alarmArmed && pageActive && alarmInterval === null) {
                    alarmInterval = window.setInterval(playAlarmOnce, 3000);
                }
            }).catch((error) => {
                alarmPlaybackPending = false;
                reportPlaybackFailure(error);
                stopAlarmCycle();
            });
        }

        function startAlarmCycle() {
            if (!pageActive || !alarmArmed || alarmInterval !== null || alarmPlaybackPending || audioBlockedUntilGesture ||
                audioUnavailable) return;
            playAlarmOnce();
        }

        function retryBlockedAlarmFromGesture() {
            if (pageActive && alarmArmed && audioBlockedUntilGesture) {
                audioBlockedUntilGesture = false;
                startAlarmCycle();
            }
        }

        async function checkNewOrders() {
            if (isCheckingOrders || !pageActive) return;
            isCheckingOrders = true;

            try {
                const response = await fetch(pollUrl, {
                    headers: {
                        Accept: 'application/json',
                    },
                    cache: 'no-store',
                    credentials: 'same-origin',
                });
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                const data = await response.json();
                if (!Array.isArray(data.new_orders)) throw new Error('Format daftar pesanan tidak valid');

                const currentConfirmedIds = new Set();
                let foundNewOrder = false;
                for (const order of data.new_orders) {
                    const id = Number(order.id);
                    if (!Number.isInteger(id) || id < 1 || order.status !== 'confirmed') continue;
                    currentConfirmedIds.add(id);
                    if (!confirmedOrderIds.has(id)) {
                        foundNewOrder = true;
                        addOrderRow(order);
                    }
                }

                if (foundNewOrder) alarmArmed = true;
                confirmedOrderIds.clear();
                currentConfirmedIds.forEach((id) => confirmedOrderIds.add(id));

                if (alarmArmed && currentConfirmedIds.size === 0) {
                    alarmArmed = false;
                    stopAlarmCycle();
                    clearAudioWarning();
                } else if (alarmArmed) {
                    startAlarmCycle();
                }
            } catch (error) {
                console.error('Gagal memeriksa pesanan koki:', error);
            } finally {
                isCheckingOrders = false;
            }
        }

        function startPolling() {
            if (pollInterval !== null) return;
            pageActive = true;
            checkNewOrders();
            pollInterval = window.setInterval(checkNewOrders, 5000);
        }

        function cleanupDashboardNotifications() {
            pageActive = false;
            if (pollInterval !== null) {
                window.clearInterval(pollInterval);
                pollInterval = null;
            }
            alarmArmed = false;
            stopAlarmCycle();
        }

        alarmAudio.addEventListener('error', () => {
            if (pageActive) {
                audioUnavailable = true;
                showAudioWarning('File alarm lokal tidak dapat dimuat.');
            }
        });
        window.addEventListener('pointerdown', retryBlockedAlarmFromGesture, true);
        window.addEventListener('keydown', retryBlockedAlarmFromGesture, true);
        window.addEventListener('pagehide', cleanupDashboardNotifications);
        window.addEventListener('pageshow', (event) => {
            if (event.persisted) startPolling();
        });

        soundStatus.textContent = 'Notifikasi suara siap dicoba';
        startPolling();
    }
}
