<template>
    <div class="channel-params-closing-rule">
        <h4 class="text-center">{{ $t("Close automatically") }}</h4>
        <dl>
            <dd>{{ $t('Enabled') }}</dd>
            <dt class="text-center">
                <toggler v-model="props.channel.config.closingRule.enabled" @input="$emit('change')"/>
            </dt>
            <dd v-tooltip="$t('channelConfigHelp_gateCloseAfterHelp')">
                {{ $t('Close after') }}
                <i class="pe-7s-help1"></i>
            </dd>
            <dt>
                <span class="input-group">
                    <input type="number"
                        step="1"
                        min="5"
                        max="480"
                        class="form-control text-center"
                        v-model="maxTimeOpenMin"
                        @change="$emit('change')">
                    <span class="input-group-addon">{{ $t('min.') }}</span>
                </span>
            </dt>
            <dd>{{ $t('Working schedule') }}</dd>
            <dt>
                <WeekScheduleCaption :schedule="props.channel.config.closingRule.activeHours" :emptyCaption="$t('everyday')"/>
                <div><a @click="changing = true">{{ $t('Change') }}</a></div>
            </dt>
        </dl>
        <modal v-if="changing"
            :header="$t('Automatic closing rule')"
            class="modal-800"
            @confirm="(changing = false) || $emit('change')">
            <DateRangePicker v-model="activeDateRange"/>
            <WeekScheduleSelector v-model="activeHours"/>
        </modal>
    </div>
</template>

<script setup>
    import {computed, ref, set} from "vue";
    import DateRangePicker from "@/direct-links/date-range-picker";
    import WeekScheduleSelector from "@/access-ids/week-schedule-selector";
    import {mapValues, pickBy} from "lodash";
    import WeekScheduleCaption from "@/access-ids/week-schedule-caption";

    const props = defineProps({channel: Object});
    const changing = ref(false);

    const maxTimeOpenMin = computed({
        get() {
            return Math.floor(props.channel.config.closingRule.maxTimeOpen / 60);
        },
        set(value) {
            set(props.channel.config.closingRule, 'maxTimeOpen', value * 60);
        }
    });

    const activeDateRange = computed({
        get() {
            return {dateStart: props.channel.config.closingRule.activeFrom, dateEnd: props.channel.config.closingRule.activeTo};
        },
        set(dates) {
            set(props.channel.config.closingRule, 'activeFrom', dates.dateStart || null);
            set(props.channel.config.closingRule, 'activeTo', dates.dateEnd || null);
        }
    });

    const activeHours = computed({
        get() {
            if (props.channel.config.closingRule.activeHours) {
                return mapValues(props.channel.config.closingRule.activeHours, (hours) => {
                    const hoursDef = {};
                    [...Array(24).keys()].forEach((hour) => hoursDef[hour] = hours.includes(hour) ? 1 : 0);
                    return hoursDef;
                });
            } else {
                return {};
            }
        },
        set(weekSchedule) {
            set(props.channel.config.closingRule, 'activeHours', mapValues(weekSchedule, (hours) => {
                return Object.keys(pickBy(hours, (selection) => !!selection)).map((hour) => parseInt(hour));
            }));
        }
    });
</script>

<style lang="scss">
    .channel-params-closing-rule {
        .schedule-block {
            display: block;
            &::after {
                content: '';
            }
        }
    }
</style>
