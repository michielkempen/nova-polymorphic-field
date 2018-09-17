<template>
	<index-select-field
		:resource-name="resourceName"
		:field="field"
	></index-select-field>
</template>

<script>
	export default {
		props: ['resourceName', 'field'],
	}
</script>
