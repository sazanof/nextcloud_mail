<template>
	<div class="search-messages">
		<input
			v-model="query"
			type="text"
			class="search-messages--input"
			:placeholder="t('mail', 'Search in mailbox...')">
		<Button v-if="query !== ''"
			class="search-messages--close"
			@click="resetFilter()">
			<template #icon>
				<Close :size="24" />
			</template>
		</Button>

		<span
			v-if="filterChanged"
			class="filter-changed" />

		<Actions>
			<ActionButton @click="moreSearchActions = true">
				<template #icon>
					<FilterOutline :size="24" />
				</template>
				{{ t("mail", "Search parameters") }}
			</ActionButton>
		</Actions>
		<Modal
			v-if="moreSearchActions"
			:title="t('mail', 'Search parameters')"
			class="search-modal"
			@close="closeSearchModal">
			<div class="modal-inner--content">
				<div class="modal-header">
					<div class="modal-inner--field">
						<div class="modal-inner-block">
							<CheckboxRadioSwitch
								:checked.sync="searchFlags"
								value="is_important"
								name="flags[]"
								type="checkbox">
								{{ t('mail', 'Important') }}
							</CheckboxRadioSwitch>
						</div>
						<div class="modal-inner-block">
							<CheckboxRadioSwitch
								:checked.sync="searchFlags"
								value="starred"
								name="flags[]"
								type="checkbox">
								{{ t('mail', 'Favorites') }}
							</CheckboxRadioSwitch>
						</div>
						<div class="modal-inner-block">
							<CheckboxRadioSwitch
								:checked.sync="searchFlags"
								value="unread"
								name="flags[]"
								type="checkbox">
								{{ t('mail', 'Unread') }}
							</CheckboxRadioSwitch>
						</div>
					</div>
				</div>

				<div class="modal-inner--field">
					<label class="modal-inner--label" for="subjectId">
						{{ t("mail", "Subject") }}
					</label>
					<div class="modal-inner--container">
						<CheckboxRadioSwitch
							id="subjectId"
							:checked.sync="searchInSubject"
							type="switch" />
					</div>
				</div>

				<div class="modal-inner--field">
					<label class="modal-inner--label" for="fromId">
						{{ t("mail", "From") }}
					</label>
					<div class="modal-inner--container">
						<Multiselect
							id="fromId"
							v-model="searchInFrom"
							label="label"
							track-by="email"
							:options="autocompleteRecipients"
							:value="searchInFrom"
							:placeholder="t('mail', 'Select recipient...')"
							:multiple="true"
							:taggable="true"
							:close-on-select="true"
							:show-no-options="false"
							:preserve-search="true"
							:max="1"
							@tag="addTag($event,'from')"
							@search-change="searchRecipients($event)" />
					</div>
				</div>

				<div class="modal-inner--field">
					<label class="modal-inner--label" for="toId">
						{{ t("mail", "To") }}
					</label>
					<div class="modal-inner--container">
						<Multiselect
							id="toId"
							v-model="searchInTo"
							label="label"
							track-by="email"
							:options="autocompleteRecipients"
							:value="searchInTo"
							:placeholder="t('mail', 'Select recipient...')"
							:multiple="true"
							:taggable="true"
							:close-on-select="true"
							:show-no-options="false"
							:preserve-search="true"
							:max="1"
							@tag="addTag($event,'to')"
							@search-change="searchRecipients($event)" />
					</div>
				</div>

				<div class="modal-inner--field">
					<label class="modal-inner--label" for="ccId">
						{{ t("mail", "Cc") }}
					</label>
					<div class="modal-inner--container">
						<Multiselect
							id="ccId"
							v-model="searchInCc"
							label="label"
							track-by="email"
							:options="autocompleteRecipients"
							:value="searchInCc"
							:placeholder="t('mail', 'Select recipient...')"
							:multiple="true"
							:taggable="true"
							:close-on-select="true"
							:show-no-options="false"
							:preserve-search="true"
							:max="1"
							@tag="addTag($event,'cc')"
							@search-change="searchRecipients($event)" />
					</div>
				</div>

				<div class="modal-inner--field">
					<label class="modal-inner--label" for="bccId">
						{{ t("mail", "Bcc") }}
					</label>
					<div class="modal-inner--container">
						<Multiselect
							id="bccId"
							v-model="searchInBcc"
							label="label"
							track-by="email"
							:options="autocompleteRecipients"
							:value="searchInCc"
							:placeholder="t('mail', 'Select recipient...')"
							:multiple="true"
							:taggable="true"
							:close-on-select="true"
							:show-no-options="false"
							:preserve-search="true"
							:max="1"
							@tag="addTag($event,'bcc')"
							@search-change="searchRecipients($event)" />
					</div>
				</div>

				<div v-if="tags.length > 0" class="modal-inner--field">
					<label for="tagsId">
						{{ t("mail", "Tags") }}
					</label>
					<div class="modal-inner--container">
						<Multiselect
							v-if="tags.length > 0"
							id="tagsId"
							v-model="selectedTags"
							class="multiselect-search-tags"
							:options="tags"
							label="displayName"
							:value="selectedTags"
							:placeholder="t('mail', 'Select tags...')"
							track-by="displayName"
							:multiple="true"
							:auto-limit="false"
							:close-on-select="false">
							<template #tag="{ option }">
								<div class="tag-group__search">
									<div
										class="tag-group__bg"
										:style="
											'background-color:' +
												(option.color !== '#fff'
													? option.color
													: '#333')" />
									<div
										class="tag-group__label"
										:style="'color:' + option.color">
										{{ option.displayName }}
									</div>
								</div>
							</template>
							<template #option="{ option }">
								{{ option.displayName }}
							</template>
						</Multiselect>
					</div>
				</div>

				<div class="modal-inner-field--center">
					<Button
						type="primary"
						@click="closeSearchModal()">
						<template #icon>
							<Magnify :size="24" />
						</template>
						{{ t('mail','Search') }}
					</Button>
					<Button
						class="button-reset-filter"
						@click="resetFilter()">
						<template #icon>
							<Close :size="24" />
						</template>
						{{ t('mail','Reset') }}
					</Button>
				</div>
			</div>
		</Modal>
	</div>
</template>

<script>
import Modal from '@nextcloud/vue/dist/Components/Modal'
import Multiselect from '@nextcloud/vue/dist/Components/Multiselect'
import Actions from '@nextcloud/vue/dist/Components/Actions'
import ActionButton from '@nextcloud/vue/dist/Components/ActionButton'
import Button from '@nextcloud/vue/dist/Components/Button'
import CheckboxRadioSwitch from '@nextcloud/vue/dist/Components/CheckboxRadioSwitch'
import FilterOutline from 'vue-material-design-icons/FilterOutline'
import Close from 'vue-material-design-icons/Close'
import Magnify from 'vue-material-design-icons/Magnify'

import debouncePromise from 'debounce-promise'
import { findRecipient } from '../service/AutocompleteService'
import uniqBy from 'lodash/fp/uniqBy'

const debouncedSearch = debouncePromise(findRecipient, 500)

export default {
	name: 'SearchMessages',
	components: {
		Modal,
		Multiselect,
		Actions,
		ActionButton,
		Button,
		CheckboxRadioSwitch,
		FilterOutline,
		Close,
		Magnify,
	},
	props: {
		mailbox: {
			type: Object,
			required: true,
		},
	},
	data() {
		return {
			query: '',
			debouncedSearchQuery: debouncePromise(this.sendQueryEvent, 700),
			autocompleteRecipients: [],
			selectedTags: [],
			moreSearchActions: false,
			searchInFrom: null,
			searchInTo: null,
			searchInCc: null,
			searchInBcc: null,
			searchInSubject: true,
			searchInMessageBody: false,
			searchFlags: [],
		}
	},
	computed: {
		tags() {
			return this.$store.getters.getTags
		},
		filterChanged() {
			return Object.entries(this.filterData).filter(([key, val]) => {
				return val !== ''
			}).length > 0
		},
		filterData() {
			return {
				to: this.searchInTo !== null && this.searchInTo.length > 0 ? this.searchInTo[0].email : '',
				from: this.searchInFrom !== null && this.searchInFrom.length > 0 ? this.searchInFrom[0].email : '',
				cc: this.searchInCc !== null && this.searchInCc.length > 0 ? this.searchInCc[0].email : '',
				bcc: this.searchInBcc !== null && this.searchInBcc.length > 0 ? this.searchInBcc[0].email : '',
				subject: this.searchInSubject && this.query.length > 1 ? this.query : '',
				tags: this.selectedTags.length > 0 ? this.selectedTags.map(item => item.id) : '',
				flags: this.searchFlags.length > 0 ? this.searchFlags.map(item => item) : '',
			}
		},
		searchQuery() {
			let _search = ''
			Object.entries(this.filterData).filter(([key, val]) => {
				if (val !== '' && val !== null) {
					if (val.indexOf(' ') !== -1) {
						val = val.replace(/ /g, '+')
					}
					_search += `${key}:${val} `
				}
				return val
			})

			return _search.trim()
		},
	},
	watch: {
		query() {
			this.debouncedSearchQuery()
		},
	},
	methods: {
		closeSearchModal() {
			this.moreSearchActions = false
			this.sendQueryEvent()
		},
		sendQueryEvent() {
			this.$emit('searchChanged', this.searchQuery)
		},
		searchRecipients(term) {
			if (term === undefined || term === '') {
				return
			}
			debouncedSearch(term).then(results => {
				this.autocompleteRecipients = uniqBy('email')(
					this.autocompleteRecipients.concat(results)
				)
			})
		},
		resetFilter() {
			const prevQuery = this.query
			this.query = ''
			this.selectedTags = []
			this.moreSearchActions = false
			this.searchInFrom = null
			this.searchInTo = null
			this.searchInCc = null
			this.searchInBcc = null
			this.searchInSubject = true
			this.searchInMessageBody = false
			this.searchFlags = []
			// Need if there is only tag filter or recipients filter
			if (prevQuery === '') {
				this.sendQueryEvent()
			}
		},
		addTag(tag, type) {
			const _tag = [{
				label: tag,
				email: tag,
			}]
			switch (type) {
			case 'to':
				this.searchInTo = _tag
				break
			case 'from':
				this.searchInFrom = _tag
				break
			case 'cc':
				this.searchInCc = _tag
				break
			case 'bcc':
				this.searchInBcc = _tag
				break
			}
		},
	},
}
</script>

<style lang="scss">
.search-messages {
	min-height: 48px;
	margin: 3px 3px 0 44px;
	position: relative;
	display: flex;
	align-items: center;

	.search-messages--input {
		width: 100%;
		border-radius: var(--border-radius-pill);
	}

	.search-messages--button {
		position: absolute;
		top: 0;
		right: -2px;
		border-radius: var(--border-radius-pill);
		border: none;
		padding: 3px 5px;
		background: none;
		transition: 0.4s;
	}

	.search-messages--button:hover {
		color: var(--color-primary-text);
		transition: 0.4s;
		background: var(--color-primary-element);
	}
}

.checkbox-radio-switch__label {
	background: none !important;
	padding: 0 !important;
	margin: 0 !important;
}
.tag-group__search {
	box-sizing: border-box;
	position: relative;
	margin: 3px 3px;
	padding: 0 6px;
}
.tag-group__bg {
	position: absolute;
	left: 0;
	right: 0;
	bottom: 0;
	top: 0;
	opacity: 0.4;
	border-radius: 14px;
	z-index: 1;
}
.tag-group__label {
	font-weight: bold;
	font-size: 12px;
	position: relative;
	z-index: 2;
}

.search-modal .modal-container {
	min-height: auto;
	overflow: visible !important;

	.modal-inner--content {
		padding: 0 0 30px 0;

		.modal-header {
			padding: 10px;
			margin-bottom: 10px;
			border-radius: 14px 14px 0 0;
			background-color: var(--color-background-dark);
			color: var(--color-main-text);
		}
	}
}
.multiselect-search-tags {
	width: 100%;
}
.multiselect-search-tags .multiselect__tags .multiselect__tags-wrap {
	flex-wrap: wrap !important;
}
.modal-inner-field--center {
	display:flex;
	align-items: center;
	justify-content: center;
	margin-top: 15px;
}
.modal-inner--field {
	display: flex;
	align-items: center;
	flex-wrap: wrap;
	justify-content: space-between;
	margin-bottom: 6px;
	padding: 0 30px;

	label {
		font-weight: bold;
	}

	.modal-inner--container {
		width: calc(100% - 120px);

		.multiselect {
			width: 100%;
		}

		.checkbox-radio-switch {
			justify-content: right;
		}
	}
}
.modal-wrapper--normal .modal-container {
	position: relative
}
.button-vue.search-messages--close.button-vue--icon-only {
	position: absolute;
	width:auto;
	height: auto;
	z-index: 5;
	right: 45px;
	box-shadow: none !important;
	background: transparent !important;
	border: none !important;
	padding: 0 !important;
}
.button-reset-filter {
	margin-left: 10px;
}
.filter-changed {
	width: 6px;
	height: 6px;
	background: var(--color-error);
	position: absolute;
	z-index: 10;
	right: 12px;
	border-radius: 50%;
	top: 7px;
}
</style>
