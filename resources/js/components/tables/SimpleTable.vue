<template>
  <v-table
    v-if="items.length"
    :density="density"
    class="bg-grey-darken-4 simple-table"
  >
    <thead>
      <tr>
        <th
          v-for="(header, i) in vHeaders"
          :key="i"
          :class="header.classes"
          v-text="header.title"
        />
      </tr>
    </thead>
    <tbody>
      <tr
        v-for="(item, i) in items"
        :key="i"
        class="bg-grey-darken-3"
      >
        <td
          v-for="(header, h) in vHeaders"
          :key="h"
          :data-label="header.title"
          :class="header.classes"
        >
          <app-buttons
            v-if="header.name === 'actions'"
            :buttons="getItemActions(item)"
            @click="$emit($event, item)"
          />
          <span
            v-else
            v-html="Table.getItemContent(header, item)"
          />
        </td>
      </tr>
      <tr v-if="withSum.length" class="!hidden !md:table-row">
        <td
          v-for="(header, index) in vHeaders"
          :key="index"
          :class="header.classes"
        >
          <span
            v-html="index === 0 ? Translator.ta('total') : applySum(header)"
          />
        </td>
      </tr>
    </tbody>
  </v-table>
  <app-empty v-else />
</template>
<script setup>
import { computed } from 'vue';
import Translator from '@/objects/Translator';
import AppButtons from '@/components/core/AppButtons.vue';
import AppEmpty from '@/components/core/AppEmpty.vue';
import Table from '@/objects/Table';
import _ from 'lodash';

const props = defineProps({
    headers: {
        type: Array,
        default: () => []
    },
    items: {
        type: Array,
        default: () => []
    },
    actions: {
        type: [Array, Function],
        default: () => []
    },
    density: {
        type: String,
        default: undefined,
        validator: (value) => ['default', 'comfortable', 'compact'].includes(value)
    },
    withSum: {
        type: Array,
        default: () => []
    }
});

const vHeaders = computed(() => { // TODO: should use Table.addCommonHeaders() ?
    const headers = props.headers.map(header => ({
        ...header,
        title: typeof header.title === 'function' ? header.title() : header.title
    }));

    if (! props.actions.length) {
        return headers;
    }
    return headers.concat([{
        name: 'actions',
        title: Translator.tl('actions'),
        classes: 'text-center'
    }]);
});

const getItemActions = (item) => {
    if (typeof props.actions === 'function') {
        return props.actions(item);
    }

    return props.actions;
}

const applySum = (header) => {
    if (!props.withSum.includes(header.key)) {
        return null;
    }
    const value = _.chain(props.items)
        .map(item => Number(_.get(item, header.key)))
        .filter(value => !isNaN(value) && ! header.markup)
        .sum()
        .value();

    return header.formatter
        ? header.formatter(value)
        : value;
}
</script>
