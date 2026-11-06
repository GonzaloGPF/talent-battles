import Translator from '@/objects/Translator';
import Utils from '@/objects/Utils';
import Formatter from '@/objects/Formatter';

const Notification = {
    getName(notification) {
        return Formatter.snakeCase(notification.type);
    },

    getTitle(notification) {
        const notificationName = Notification.getName(notification);

        return Translator.t(`notifications.${ notificationName }_title`);
    },

    getMessage(notification) {
        const notificationName = Notification.getName(notification);

        return Translator.t(`notifications.${ notificationName }_message`, Utils.removeEmpty({ ...notification.message_keys }))
    },

    toFlashMessage(notification) {
        return {
            message: Notification.getTitle(notification),
            type: notification.level
        }
    }
}

export default Notification;
