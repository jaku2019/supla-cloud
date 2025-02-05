<template>
    <PendingChangesPage :header="item.id ? $t('Edit reaction') : $t('New reaction')" :is-pending="hasPendingChanges"
        :deletable="!!item.id" :cancellable="!!item.id"
        @delete="deleteConfirm = true"
        @cancel="cancelChanges()"
        @save="submitForm()">
        <div class="channel-reaction row mt-3">
            <div class="col-sm-6">
                <transition-expand>
                    <div class="alert alert-danger" v-if="displayValidationErrors && !trigger">
                        {{ $t('Please select a condition') }}
                    </div>
                </transition-expand>
                <ChannelReactionConditionChooser :subject="owningChannel" v-model="trigger" @input="onChanged()" class="mb-3"/>
            </div>
            <div class="col-sm-6">
                <transition-expand>
                    <div class="alert alert-danger" v-if="displayValidationErrors && (!action || !targetSubject)">
                        {{ $t('Please select an action') }}
                    </div>
                </transition-expand>
                <SubjectDropdown v-model="targetSubject" class="mb-3" channels-dropdown-params="io=output&hasFunction=1">
                    <div v-if="targetSubject" class="mt-3">
                        <ChannelActionChooser :subject="targetSubject" :alwaysSelectFirstAction="true" v-model="action"
                            @input="onChanged()"/>
                    </div>
                </SubjectDropdown>
            </div>
        </div>
        <modal-confirm v-if="deleteConfirm"
            class="modal-warning"
            @confirm="deleteReaction()"
            @cancel="deleteConfirm = false"
            :header="$t('Are you sure you want to delete this reaction?')"
            :loading="loading">
        </modal-confirm>
    </PendingChangesPage>
</template>

<script>
    import ChannelReactionConditionChooser from "@/channels/reactions/channel-reaction-condition-chooser.vue";
    import SubjectDropdown from "@/devices/subject-dropdown.vue";
    import ChannelActionChooser from "@/channels/action/channel-action-chooser.vue";
    import ActionableSubjectType from "@/common/enums/actionable-subject-type";
    import {successNotification} from "@/common/notifier";
    import PendingChangesPage from "@/common/pages/pending-changes-page.vue";
    import TransitionExpand from "@/common/gui/transition-expand.vue";
    import {deepCopy} from "@/common/utils";

    export default {
        components: {TransitionExpand, PendingChangesPage, ChannelActionChooser, SubjectDropdown, ChannelReactionConditionChooser},
        props: {
            item: Object,
        },
        data() {
            return {
                trigger: undefined,
                targetSubject: undefined,
                action: undefined,
                hasPendingChanges: false,
                deleteConfirm: false,
                loading: false,
                displayValidationErrors: false,
            };
        },
        beforeMount() {
            if (this.item.id) {
                this.initFromItem();
            }
        },
        methods: {
            onChanged() {
                this.hasPendingChanges = true;
            },
            cancelChanges() {
                this.initFromItem();
            },
            initFromItem() {
                this.displayValidationErrors = false;
                const item = deepCopy(this.item);
                this.trigger = item.trigger || {};
                this.targetSubject = item.subject;
                this.action = item.actionId ? {id: item.actionId, param: item.actionParam} : undefined;
                this.$nextTick(() => this.hasPendingChanges = false);
            },
            submitForm() {
                this.displayValidationErrors = true;
                if (this.reaction.isValid) {
                    const updateFunc = this.item.id ? 'updateReaction' : 'addNewReaction';
                    this.loading = true;
                    this[updateFunc]()
                        .then(() => this.hasPendingChanges = false)
                        .then(() => this.displayValidationErrors = false)
                        .finally(() => this.loading = false);
                }
            },
            addNewReaction() {
                return this.$http.post(`channels/${this.owningChannel.id}/reactions?include=subject,owningChannel`, this.reaction)
                    .then((response) => {
                        this.$emit('add', response.body);
                        successNotification(this.$t('Success'), this.$t('The reaction has been added'));
                    });
            },
            updateReaction() {
                return this.$http.put(`channels/${this.owningChannel.id}/reactions/${this.item.id}?include=subject,owningChannel`, this.reaction)
                    .then((response) => {
                        this.$emit('update', response.body);
                        successNotification(this.$t('Success'), this.$t('The reaction has been changed'));
                    });
            },
            deleteReaction() {
                this.loading = true;
                this.$http.delete(`channels/${this.owningChannel.id}/reactions/${this.item.id}`)
                    .then(() => this.$emit('delete'))
                    .finally(() => this.loading = false);
            }
        },
        computed: {
            subjectCaption() {
                if (this.targetSubject.ownSubjectType === ActionableSubjectType.NOTIFICATION) {
                    return '';
                }
                return this.targetSubject.caption || `ID${this.targetSubject.id} ${this.$t(this.targetSubject.function.caption)}`;
            },
            owningChannel() {
                return this.item?.owningChannel;
            },
            reaction() {
                return {
                    trigger: this.trigger,
                    subjectId: this.targetSubject?.id,
                    subjectType: this.targetSubject?.ownSubjectType,
                    actionId: this.action?.id,
                    actionParam: this.action?.param,
                    isValid: !!(this.trigger && this.action && this.targetSubject),
                };
            },
        }
    }
</script>

<style lang="scss" scoped>
    .step-link {
        font-size: 1.3em;
        text-align: center;
        display: block;
    }
</style>
