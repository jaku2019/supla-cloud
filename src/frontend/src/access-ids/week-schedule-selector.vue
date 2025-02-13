<template>
    <div>
        <select class="form-control hidden visible-xs mb-3"
            v-model="mobileWeekdayDisplay">
            <option
                v-for="(weekday, $index) in ['Mondays', 'Tuesdays', 'Wednesdays', 'Thursdays', 'Fridays', 'Saturdays', 'Sundays']"
                :value="$index + 1"
                :key="'mobileWeekday' + $index">
                {{ $t(weekday) }}
            </option>
        </select>
        <table :class="['week-schedule-selector', 'selection-mode-' + selectionMode, 'mobile-weekday-' + mobileWeekdayDisplay]"
            v-if="temporaryModel">
            <thead>
            <tr>
                <th class="hour-header"></th>
                <!-- i18n: ['Mondays', 'Tuesdays', 'Wednesdays', 'Thursdays', 'Fridays', 'Saturdays', 'Sundays'] -->
                <th v-for="(weekday, $index) in ['Mondays', 'Tuesdays', 'Wednesdays', 'Thursdays', 'Fridays', 'Saturdays', 'Sundays']"
                    :key="weekday"
                    :class="['hidden-xs weekday-header ellipsis', 'weekday-column-' + ($index + 1)]">
                    {{ $t(weekday) }}
                </th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="hour in [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23]"
                :key="hour">
                <th scope="row"
                    class="hour-header ellipsis">
                    {{ hourLabelStart(hour) }} - {{ hourLabelEnd(hour) }}
                </th>
                <td v-for="weekday in [1,2,3,4,5,6,7]"
                    :class="['hidden-xs', 'weekday-column-' + weekday]"
                    :key="'0' + hour + weekday">
                    <div :class="['time-slot', {'green': temporaryModel[weekday][hour]}]"
                        @mousedown="startSelection(weekday, hour)"
                        @mouseenter="expandSelection(weekday, hour)"
                        @mouseup="finishSelection()">
                        &nbsp;
                    </div>
                </td>
            </tr>
            </tbody>
            <tfoot>
            <tr>
                <th></th>
                <th v-for="weekday in [1,2,3,4,5,6,7]"
                    :class="['hidden-xs text-center copy-buttons', 'weekday-column-' + weekday]"
                    :key="'copypaste' + weekday">
                    <a class="mx-1"
                        v-if="!copyFrom || copyFrom === weekday"
                        @click="copyFrom = (copyFrom === weekday ? undefined : weekday)">
                        <span :class="['pe-7s-copy-file', {'copy-from': copyFrom === weekday}]"></span>
                    </a>
                    <a class="mx-1"
                        @click="copyDay(copyFrom, weekday)"
                        v-if="copyFrom && copyFrom !== weekday">
                        <span class="pe-7s-paint"></span>
                    </a>
                </th>
            </tr>
            </tfoot>
        </table>
    </div>
</template>

<script>
    import {cloneDeep} from "lodash";
    import {DateTime} from "luxon";

    export default {
        components: {},
        props: ['value'],
        data() {
            return {
                model: undefined,
                temporaryModel: undefined,
                selectionMode: undefined,
                selectionStartCoords: undefined,
                copyFrom: undefined,
                mouseUpCatcher: undefined,
                mobileWeekdayDisplay: 1,
            };
        },
        mounted() {
            this.model = {};
            const defaultValue = this.value && Object.keys(this.value).length ? 0 : 1;
            [...Array(7).keys()].forEach((weekday) => {
                weekday += 1;
                const hours = {};
                [...Array(24).keys()].forEach((hour) => {
                    hours[hour] = (this.value && this.value[weekday] && this.value[weekday][hour]) || defaultValue;
                });
                this.$set(this.model, weekday, hours);
            });
            this.temporaryModel = cloneDeep(this.model);
            this.mouseUpCatcher = () => this.finishSelection();
            window.addEventListener('mouseup', this.mouseUpCatcher);
        },
        beforeDestroy() {
            window.removeEventListener('mouseup', this.mouseUpCatcher);
        },
        methods: {
            hourLabelStart(hour) {
                const start = DateTime.fromFormat(('0' + hour).substr(-2), 'HH');
                return start.toLocaleString(DateTime.TIME_SIMPLE);
            },
            hourLabelEnd(hour) {
                const end = DateTime.fromFormat(('0' + hour).substr(-2), 'HH').endOf('hour');
                return end.toLocaleString(DateTime.TIME_SIMPLE);
            },
            startSelection(weekday, hour) {
                this.copyFrom = undefined;
                this.selectionStartCoords = {weekday, hour};
                this.selectionMode = this.temporaryModel[weekday][hour] ? 0 : 1;
                this.expandSelection(weekday, hour);
            },
            expandSelection(weekday, hour) {
                if (this.selectionStartCoords) {
                    this.temporaryModel = cloneDeep(this.model);
                    const fromWeekday = Math.min(this.selectionStartCoords.weekday, weekday);
                    const toWeekday = Math.max(this.selectionStartCoords.weekday, weekday);
                    const fromHour = Math.min(this.selectionStartCoords.hour, hour);
                    const toHour = Math.max(this.selectionStartCoords.hour, hour);
                    for (let i = fromWeekday; i <= toWeekday; i++) {
                        for (let j = fromHour; j <= toHour; j++) {
                            this.temporaryModel[i][j] = this.selectionMode;
                        }
                    }
                }
            },
            finishSelection() {
                if (this.selectionStartCoords) {
                    this.updateModel();
                    this.selectionStartCoords = undefined;
                }
            },
            updateModel() {
                this.model = this.temporaryModel;
                this.$emit('input', cloneDeep(this.model));
            },
            copyDay(copyFrom, copyTo) {
                this.temporaryModel[copyTo] = cloneDeep(this.temporaryModel[copyFrom]);
                this.updateModel();
            }
        },
        computed: {},
        watch: {}
    };
</script>

<style lang="scss">
    @import '../styles/variables';
    @import '../styles/mixins';

    table.week-schedule-selector {
        user-select: none;
        table-layout: fixed;
        width: 100%;

        @include on-xs-and-down {
            max-width: 90%;
        }

        .weekday-header {
            text-align: center;
        }
        .hour-header {
            text-align: right;
            padding-right: 5px;
            white-space: nowrap;
        }
        th {
            font-weight: normal;
        }
        tr {
            height: 1px; // allows for the links inside tds fill the whole height https://stackoverflow.com/a/34781198/878514
        }
        td {
            height: 100%;
            width: 100%;
            padding: 1px;
            vertical-align: middle;
        }
        .time-slot {
            cursor: pointer;
            display: block;
            height: 100%;
            background: $supla-grey-light;
            &:hover {
                background: lighten($supla-green, 40%);
            }
            &.green {
                background: $supla-green;
                &:hover {
                    background: lighten($supla-green, 20%);
                }
            }
            @media (hover: none) {
                background: $supla-grey-light !important;
                &.green {
                    background: $supla-green !important;
                }
            }
        }
        .copy-buttons {
            font-size: 1.3em;
            padding-top: .3em;
            a {
                color: $supla-grey-dark;
                .copy-from {
                    font-weight: bold;
                }
            }
        }
        @for $i from 1 through 7 {
            &.mobile-weekday-#{$i} {
                .hidden-xs.weekday-column-#{$i} {
                    display: table-cell !important;
                }
            }
        }
    }
</style>
