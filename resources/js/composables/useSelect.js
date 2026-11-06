import { computed, ref, unref } from 'vue';
import { useConstant } from '@/stores/constants';
import SelectItems from '@/objects/SelectItems';

/**
 *
 * @param value
 * @param {string} src
 * @param multiple
 * @param {?ref<string>} search
 * @param {?function} customFilter
 * @param {?ref<Array>} customOptions
 * @returns {{getToggleSelectIcon: ((function(*, *=, *=): (string))|*), getSelectedItems: ((function(*, *, *=): ([]|null))|*), items: ComputedRef<*|unknown[]>, getItemValue: (function(*): string|number|*)}}
 */
export default (value, src, multiple = false, search = null, customFilter, customOptions) => {
    const { getConstants } = useConstant();
    search = ref(search);
    customOptions = ref(customOptions);

    const items = computed(() => {
            let items = customOptions?.value || getConstants(src);

            return SelectItems.filterAndOrder(items, search.value, customFilter);
        }
    );

    const selectedItems = computed(() => SelectItems.selectedItems(items.value, unref(value), unref(multiple)));

    const selectionIcon = computed(() => SelectItems.selectionIcon(items.value, unref(value), unref(multiple)));

    return {
        items,
        selectedItems,
        selectionIcon,
    }
}
