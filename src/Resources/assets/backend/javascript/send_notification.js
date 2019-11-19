import documentReady from 'document-ready';

class SendNotification {
    constructor() {
        this.terminate = false;
        this.sendNotificationWrapper = document.querySelector('#tl_send_notification_action');
        this.errortWrapper = document.querySelector('#error');
        this.sendNotificationProgressbar = this.sendNotificationWrapper.querySelector('.progress-bar-inner');
        this.sendDelay = 20;
        this.debug = ((location.href.search('app_dev.php') > 1) ? '/app_dev.php' : '');
    }

    init() {
        let self = this;
        document.querySelector('#terminate').addEventListener('click', function (event) {
            event.preventDefault();

            self.terminate = true;
        });

        this.sendNotification();
    }

    sendNotification() {
        if (this.terminate) {
            return false;
        }

        let route = this.debug + '/contao/cb/member/send_password/send/notification';
        let self = this;
        this.sendNotificationWrapper.classList.remove('not_active');

        this.request(route, function (response) {
            self.sendNotificationProgressbar.style.width = response.progress + '%';
            if (response.progress < 100) {
                setTimeout(function () {
                    self.sendNotification();
                }, response.sendDelay);

                return false;
            }

            self.sendNotificationProgressbar.style.width = response.progress + '%';
            self.sendNotificationProgressbar.addEventListener('transitionend', function () {
                self.finish();
            });
        });
    }

    finish() {
        location.href = this.debug + '/contao?do=member';
    }

    request(route, callback) {
        if (this.terminate) {
            return false;
        }

        let request = new XMLHttpRequest();
        let self = this;

        request.onreadystatechange = function () {
            if (4 === request.readyState) {
                let response = JSON.parse(request.responseText);
                if (undefined === response.error) {
                    callback(response);

                    return true;
                }

                self.errortWrapper.innerHTML = response.error;
            }
        };

        request.open('GET', route);
        request.send();
    }
}

documentReady(function () {
    const importer = new SendNotification();
    importer.init();
});
