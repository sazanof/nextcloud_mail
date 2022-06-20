<template>
	<Popover :open="popover">
		<template>
			<div class="popover-details">
				<div class="popover-details--field">
					<div class="popover-details--field__head">{{ t('mail', 'From') }}</div>
					<div class="popover-details--field__inner">
						<span>{{ envelope.from[0].label }}</span>
						<span>{{ envelope.from[0].email }}</span>
					</div>
				</div>
				<div v-if="envelope.to.length > 0" class="popover-details--to">
					<div class="popover-details--field">
						<div class="popover-details--field__head">{{ t('mail', 'To') }}</div>
						<div class="popover-details--field__inner">
							<p v-for="to in envelope.to" :key="to.email">
								<span>{{ to.label }}</span>
								<span>&lt;{{ to.email }}&gt;</span>
							</p>
						</div>
					</div>
				</div>

				<div v-if="envelope.cc.length > 0" class="popover-details--cc">
					<div class="popover-details--field">
						<div class="popover-details--field__head">{{ t('mail', 'Cc') }}</div>
						<div class="popover-details--field__inner">
							<p v-for="cc in envelope.cc" :key="cc.email">
								<span>{{ cc.label }}</span>
								<span>&lt;{{ cc.email }}&gt;</span>
							</p>
						</div>
					</div>
				</div>
			</div>
		</template>
	</Popover>
</template>

<script>
import { generateUrl } from '@nextcloud/router'

import Avatar from '@nextcloud/vue/dist/Components/Avatar'
import Popover from '@nextcloud/vue/dist/Components/Popover'
export default {
	name: 'ThreadEnvelopePopover',
	components: {
		Avatar,
		Popover
	},
	props: {
		popover: {
			type: Boolean,
			default: false
		},
		envelope: {
			type: Object,
			required: true
		}
	},
	created(){
		console.log(this.envelope)
	},
	data(){
		return {
			
		}
	},
	computed: {
		avatarUrlAbsolute() {
			if (!this.avatarUrl) {
				return
			}
			if (this.avatarUrl.startsWith('http')) {
				return this.avatarUrl
			}

			return window.location.protocol + '//' + window.location.host + generateUrl(this.avatarUrl)
		},
	}
}
</script>

<style lang="scss" scoped>
	.popover-details {
		padding:16px;

		.popover-details--field {
			display:flex;
			align-items: flex-start;
			flex-wrap: wrap;
			line-height: normal;
			margin-bottom: 4px;

			.popover-details--field__head {
				width: 60px;
			}

			.popover-details--field__inner {
				margin-left: 5px;
				width: calc(100% - 65px);
				max-height: 120px;
				overflow-y: auto
			}

			span {
				display:block
			}

			span:first-child {
				font-weight: bold;
			}
		}
	}
</style>
