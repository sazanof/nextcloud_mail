<template>
	<span  :data-timestamp="timestamp * 1000" :title="formatted">{{ title }}</span>
</template>

<script>
import moment from '@nextcloud/moment'

export default {
	name: 'Moment',
	props: {
		timestamp: {
			type: Number,
			required: true,
		},
		format: {
			type: String,
			default: 'LLL',
		},
	},
	computed: {
		title() {
			return moment.unix(this.timestamp).format(this.format)
		},
		formatted() {
			return moment.unix(this.timestamp).fromNow()
		},
	},
}
</script>
