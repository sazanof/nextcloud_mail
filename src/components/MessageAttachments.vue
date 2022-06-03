<!--
  - @copyright 2018 Christoph Wurst <christoph@winzerhof-wurst.at>
  -
  - @author 2018 Christoph Wurst <christoph@winzerhof-wurst.at>
  -
  - @license GNU AGPL version 3 or any later version
  -
  - This program is free software: you can redistribute it and/or modify
  - it under the terms of the GNU Affero General Public License as
  - published by the Free Software Foundation, either version 3 of the
  - License, or (at your option) any later version.
  -
  - This program is distributed in the hope that it will be useful,
  - but WITHOUT ANY WARRANTY; without even the implied warranty of
  - MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  - GNU Affero General Public License for more details.
  -
  - You should have received a copy of the GNU Affero General Public License
  - along with this program.  If not, see <http://www.gnu.org/licenses/>.
  -->

<template>
	<div class="mail-message-attachments" :class="hasNextLine ? 'has-next-line' : ''">
		<div v-if="hasNextLine"
			class="show-more-attachments"
			@click="isToggled = !isToggled">
			<ChevronDown v-if="isToggled" :size="24" />
			<ChevronUp v-if="!isToggled" :size="24" />
			{{ n('mail', '{count} attachment', '{count} attachments', attachments.length, { count: attachments.length }) }}
		</div>
		<p v-if="moreThanOne" class="attachments-button-wrapper">
			<span class="attachment-link"
				:disabled="savingToCloud"
				@click="saveAll">
				<CloudDownload v-if="!savingToCloud" :size="18" />
				<Loading v-else class="spin" :size="18" />
				{{ t('mail', 'Save all to Files') }}
			</span>
			<span class="attachment-link"
				@click="downloadZip">
				<Download :size="18" />
				{{ t('mail', 'Download Zip') }}
			</span>
		</p>
		<div class="mail-message-attachments--wrapper" :class="(hasNextLine === true && isToggled === true) ? 'hide' : ''">
			<div class="attachments">
				<MessageAttachment
					v-for="attachment in attachments"
					:id="attachment.id"
					ref="attachments"
					:key="attachment.id"
					:file-name="attachment.fileName"
					:size="attachment.size"
					:url="attachment.downloadUrl"
					:is-image="attachment.isImage"
					:is-calendar-event="attachment.isCalendarEvent"
					:mime="attachment.mime"
					:mime-url="attachment.mimeUrl"
					@click="showViewer(attachment)" />
				<AttachmentImageViewer v-if="attachmentImageURL && showPreview"
					:url="attachmentImageURL"
					@close="showPreview = false" />
			</div>
		</div>
	</div>
</template>

<script>
import { generateUrl } from '@nextcloud/router'
import { getFilePickerBuilder } from '@nextcloud/dialogs'
import { saveAttachmentsToFiles } from '../service/AttachmentService'

import MessageAttachment from './MessageAttachment'
import Logger from '../logger'
import AttachmentImageViewer from './AttachmentImageViewer'

import Download from 'vue-material-design-icons/Download'
import CloudDownload from 'vue-material-design-icons/CloudDownload'
import Loading from 'vue-material-design-icons/Loading'
import ChevronDown from 'vue-material-design-icons/ChevronDown'
import ChevronUp from 'vue-material-design-icons/ChevronUp'

export default {
	name: 'MessageAttachments',
	components: {
		AttachmentImageViewer,
		MessageAttachment,
		Download,
		CloudDownload,
		Loading,
		ChevronDown,
		ChevronUp,
	},
	props: {
		envelope: {
			required: true,
			type: Object,
		},
		attachments: {
			type: Array,
			required: true,
		},
	},
	data() {
		return {
			savingToCloud: false,
			showPreview: false,
			attachmentImageURL: '',
			hasNextLine: false,
			isToggled: false,
		}
	},
	computed: {
		moreThanOne() {
			return this.attachments.length > 1
		},
		zipUrl() {
			return generateUrl('/apps/mail/api/messages/{id}/attachments', {
				id: this.envelope.databaseId,
			})
		},
	},
	mounted() {
		let prevTop = null
		this.$nextTick(function() {
			if (this.$refs.attachments) {
				this.$refs.attachments.some((attachment, i) => {
					const top = attachment.$el.getBoundingClientRect().top
					if (prevTop !== null && prevTop !== top) {
						this.isToggled = true
						this.hasNextLine = true
						return true
					} else {
						prevTop = top
					}
					return false
				})
			}
		})

	},
	methods: {
		saveAll() {
			const picker = getFilePickerBuilder(t('mail', 'Choose a folder to store the attachments in'))
				.setMultiSelect(false)
				.addMimeTypeFilter('httpd/unix-directory')
				.setModal(true)
				.setType(1)
				.allowDirectories(true)
				.build()

			const saveAttachments = (id) => (directory) => {
				return saveAttachmentsToFiles(id, directory)
			}
			const id = this.$route.params.threadId

			return picker
				.pick()
				.then((dest) => {
					this.savingToCloud = true
					return dest
				})
				.then(saveAttachments(id))
				.then(() => Logger.info('saved'))
				.catch((error) => Logger.error('not saved', { error }))
				.then(() => (this.savingToCloud = false))
		},
		downloadZip() {
			window.location = this.zipUrl
		},
		showViewer(attachment) {
			if (attachment.isImage) {
				this.showPreview = true
				this.attachmentImageURL = attachment.downloadUrl
			}
		},
	},
}
</script>

<style lang="scss">
.attachments {
	width: 100%;
    box-sizing: border-box;
    position: relative;
    display: flex;
    flex-wrap: wrap;
    margin: 10px 0;
}

/* show icon + text for Download all button
		as well as when there is only one attachment */
.attachments-button-wrapper {
	text-align: center;
	display: flex;
	align-items: center;
}

.show-more-attachments {
	display: flex;
    align-items: center;
	cursor: pointer;
	padding: 0 8px;
	color: var(--color-text-lighter);

	&:hover {
		color: var(--color-main-text);
	}
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  50% {
    transform: rotate(180deg);
  }
  100% {
    transform: rotate(360deg);
  }
}

.spin {
  animation: spin 1s linear infinite;
}

.attachment-link {
	cursor: pointer;
	display:flex;
	align-items: center;
	color: var(--color-text-lighter);

	&:hover {
		color: var(--color-main-text);
	}

	span {
		margin: 0 4px 0 10px;
	}
}

.oc-dialog {
	z-index: 10000000;
}
.mail-message-attachments {
	display:flex;
	flex-wrap: wrap;
	padding: 0 6px 0 46px;
	margin-top: 4px;
}
.mail-message-attachments--wrapper {
	display:flex;
	width:100%;
	height:auto;
	overflow: hidden;
	max-height: none;
}

.mail-message-attachments--wrapper.hide {
	display:flex;
	max-height: 67px;
}
</style>
