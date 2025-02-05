<template>
    <div>
        <select class="selectpicker other-actions-picker"
            ref="dropdown"
            data-live-search="true"
            data-width="100%"
            data-style="btn-default btn-wrapped"
            v-model="chosenAction"
            @change="$emit('input', chosenAction)">
            <option v-for="action in actionsForDropdown"
                :key="action.id"
                :value="action"
                :data-content="actionHtml(action)">
                {{ $t(action.label) }}
            </option>
        </select>
    </div>
</template>

<script>
    import Vue from "vue";
    import $ from "jquery";
    import "@/common/bootstrap-select";
    import ActionableSubjectType from "@/common/enums/actionable-subject-type";
    import ChannelFunctionAction from "../../common/enums/channel-function-action";

    export default {
        props: ['value', 'filter'],
        components: {},
        data() {
            return {
                availableActions: [
                    {
                        id: ChannelFunctionAction.AT_DISABLE_LOCAL_FUNCTION,
                        label: 'Disable local function', // i18n
                        description: 'Disables local device function and does nothing more.', // i18n
                        icon: 'power',
                        ownSubjectType: ActionableSubjectType.OTHER,
                    },
                    {
                        id: ChannelFunctionAction.AT_FORWARD_OUTSIDE,
                        label: 'Publish to integrations', // i18n
                        description: 'Publishes the event to integrated services like MQTT or webhooks.', // i18n
                        icon: 'speaker',
                        ownSubjectType: ActionableSubjectType.OTHER,
                    },
                ],
                chosenAction: undefined,
            };
        },
        mounted() {
            this.setActionFromModel();
            this.initSelectPicker();
        },
        methods: {
            actionHtml(action) {
                return `
                <div class='subject-dropdown-option flex-left-full-width'>
                    <div class="labels full">
                        <h4><span class="line-clamp line-clamp-2">${this.$t(action.label)}</span></h4>
                        <p>${this.$t(action.description)}</p>
                    </div>
                    <div class="icon"><span class="pe-7s-${action.icon}"></span></div>
                </div>
                `;
            },
            updateDropdownOptions() {
                Vue.nextTick(() => $(this.$refs.dropdown).selectpicker('refresh'));
            },
            setActionFromModel() {
                if (this.value) {
                    this.chosenAction = this.availableActions.filter(ch => ch.id == this.value.id)[0];
                } else {
                    this.chosenAction = undefined;
                }
            },
            initSelectPicker() {
                Vue.nextTick(() => $(this.$refs.dropdown).selectpicker(this.selectOptions));
            },
        },
        computed: {
            actionsForDropdown() {
                this.updateDropdownOptions();
                const filter = this.filter || (() => true);
                return this.availableActions.filter(filter);
            },
            selectOptions() {
                return {
                    noneSelectedText: this.$t('choose the action'),
                    liveSearchPlaceholder: this.$t('Search'),
                    noneResultsText: this.$t('No results match {0}'),
                };
            },
        },
        watch: {
            value() {
                this.setActionFromModel();
                this.updateDropdownOptions();
            },
            '$i18n.locale'() {
                $(this.$refs.dropdown).selectpicker('destroy');
                this.initSelectPicker();
            },
        }
    };
</script>

<style lang="scss">
    .other-actions-picker {
        .subject-dropdown-option {
            .icon {
                font-size: 3em;
            }
        }
    }
</style>
