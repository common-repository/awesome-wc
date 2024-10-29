<?php defined('ABSPATH') or exit; ?>
<div id="stAppWrapper">
    <v-app id="awp-<?php echo STAwesome()->fn->getNewAppID(); ?>">
        <st-wc-business-hours-admin v-cloak inline-template class="transparent" :forms='<?php echo $forms; ?>' :settings='<?php echo $settings; ?>'>
            <div class="st-wc-business-hours-admin st-content">

                <?php wc_get_template('app-bar.php', [], '', STAwesome()->getTemplatesPath()); ?>

                <div class="pa-5">
                    <v-card flat tile class="w-100">
                        <v-card-title class="headline py-0">
                            <?php echo __('Component configuration', 'awesome-plugin'); ?>
                            <v-spacer></v-spacer>
                            <v-btn class="mr-3" @click="$root.copyToClipboard('[st_wc_awesome_business_hours]')" title="click to copy" rounded small color="#eee">
                                Shortcode
                                <v-icon class="ml-3">mdi-content-copy</v-icon>
                            </v-btn>
                            <v-switch v-model="enabled" @change="update('enabled', $event)"></v-switch>
                        </v-card-title>
                    </v-card>

                    <v-row class="align-self-start">
                        <v-col cols="12" md="6" style="border-right: 1px solid #cdcdcd;">
                            <v-card flat tile>
                                <v-card-title>General</v-card-title>
                                <v-card-subtitle>These settings control the Business Hours component.</v-card-subtitle>
                                <v-card-text>
                                <v-container fluid px-0>
                                    <v-expansion-panels flat tile dense class="mb-3">
                                        <st-wc-day v-for="day in days" :key="day.option" :day="day.option" :data="settings[day.option]"></st-wc-day>
                                    </v-expansion-panels>
                                    <st-wc-switch v-for="(vswitch, index) in switches"
                                    :key="'switch-' + index" :option="vswitch.option"
                                    :optional="vswitch.optional" :title="vswitch.title">
                                    </st-wc-switch>
                                    <st-wc-select v-for="(select, index) in selects"
                                    :key="'select-' + index" :option="select.option">
                                    </st-wc-select>
                                    <st-wc-color-picker v-show="highlightCurrent" option="color"></st-wc-color-picker>
                                </v-container>
                                </v-card-text>
                            </v-card>
                        </v-col>
                        <v-col cols="12" md="6" class="align-self-start st-wc-sticky">
                            <v-card flat tile>
                                <v-card-text>
                                    <v-container px-0 fluid>
                                    <v-row>
                                        <v-col class="px-0" cols="12">Preview</v-col>
                                        <st-wc-business-hours :settings="settings" :active="false"></st-wc-business-hours>
                                    </v-row>
                                    </v-container>
                                </v-card-text>
                            </v-card>
                        </v-col>
                    </v-row>
                </div>
                <st-wc-alert></st-wc-alert>
            </div>
        </st-wc-business-hours-admin>
    </v-app>
</div>