import EventBus from '@/events/EventBus';

export default (events, tableRef) => {
    if (! Array.isArray(events)) {
        events = [events]
    }

    events
        .filter(value => typeof value === 'string' && value)
        .forEach(eventName => EventBus.on(eventName, refreshTable))

    function refreshTable() {
        tableRef.refresh()
    }
}
