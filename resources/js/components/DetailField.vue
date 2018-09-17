<template>
	<div>

		<detail-select-field
			:resource="resource"
			:resource-id="resourceId"
			:resource-name="resourceName"
			:field="field"
		/>

		<div v-for="type in field.types" v-if="type.active">
			<div v-for="typeField in type.fields" :ref="'type-'+type.value+'-fields'">
				<component
					:is="'detail-' + typeField.component"
					:resource-id="resourceId"
					:resource-name="resourceName"
					:field="typeField"
					:ref="'field-' + typeField.attribute"
				/>
			</div>
		</div>

	</div>
</template>

<script>
	export default {
		props: ['resource', 'resourceName', 'resourceId', 'field'],

		created() {
			console.log(this.field.types);
		}
	}
</script>
