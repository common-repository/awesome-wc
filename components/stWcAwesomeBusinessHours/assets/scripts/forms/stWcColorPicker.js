Vue.component('st-wc-color-picker', {
    template:  `
    <v-row>
        <v-col cols="12" md="6">
            Highlight Color
        </v-col>
        <v-col cols="12" md="6">
            <v-color-picker v-model="value" mode="hexa" hide-mode-switch flat @input="update('highlightColor', $event)"></v-color-picker>
        </v-col>
    </v-row>
    `,
    props: { option: { type: String }},
    data() { 
        return { 
            value : this.$parent.$parent[this.option]
        }
    },
    methods: {
        /** 
         *  On value change, emit update event
         *  @param {String} option - name of setting
         *  @param {Mixed} value - setting
        */
        update(option, value) {
            stBus.$emit('update-setting', { option: option, value: value.hex, encode: false });
        }
    },
});