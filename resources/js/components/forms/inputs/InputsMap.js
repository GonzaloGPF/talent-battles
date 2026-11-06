import iText from '@/components/forms/inputs/iText.vue';
import iBoolean from '@/components/forms/inputs/iBoolean.vue';
import iSelect from '@/components/forms/inputs/iSelect.vue';
import iCheck from '@/components/forms/inputs/iCheck.vue';
import iAutoComplete from '@/components/forms/inputs/iAutoComplete.vue';
import iDate from '@/components/forms/inputs/iDate.vue';
import iNumber from '@/components/forms/inputs/iNumber.vue';
import iNumberRange from '@/components/forms/inputs/iNumberRange.vue';
import iComboBox from '@/components/forms/inputs/iComboBox.vue';

const map = {
    text: iText,
    boolean: iBoolean,
    select: iSelect,
    check: iCheck,
    auto: iAutoComplete,
    date: iDate,
    number: iNumber,
    number_range: iNumberRange,
    combo: iComboBox,
};

export default {
    getComponent(inputName) {
        return map[inputName || 'text'];
    }
}
