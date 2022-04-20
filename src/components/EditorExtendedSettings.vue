<!--
  - @copyright 2022 Mikhail Sazanov <m@sazanof.ru>
  -
  - @author 2022 Mikhail Sazanov <m@sazanof.ru>
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
    <div>
		<p v-if="editorMode === 'richtext'">
			<input id="editor_more_features"
				v-model="editorMoreFeatures"
				type="checkbox"
				class="checkbox"
				value="1" :checked="editorMoreFeatures === 1">
			<label for="editor_more_features">
				{{ t('mail', 'Use more formatting options') }}
			</label>
			
		</p>
        <p v-if="editorMode === 'richtext'">
			<label for="editor_position">
				{{ t('mail', 'Editor layout style') }}
			</label>
			<select 
                id="editor_position" 
                v-model="editorPosition">
				<option value="balloon" :selected="editorPosition === 'balloon'">{{t('mail','Balloon style')}}</option>
				<option value="classic" :selected="editorPosition === 'classic'">{{t('mail','Classic style')}}</option>
			</select>
		</p>
    </div>
</template>

<script>
import Logger from '../logger'

export default {
    props: {
        account: {
            type: Object,
            required: true
        }
    },
    data(){
        console.log(this.account);
        return {
            editorMode: this.account.editorMode,
            editorPosition: this.account.editorPosition,
            editorMoreFeatures: this.account.editorMoreFeatures
        }
    },
    watch: {
        editorPosition(val, oldVal){
            this.$store
				.dispatch('patchAccount', {
					account: this.account,
					data: {
						editorPosition: val,
					},
				})
				.then(() => {
					Logger.info('editor position updated')
				})
				.catch((error) => {
					Logger.error('could not update editor position', { error })
					this.editorPosition = oldVal
					throw error
				})
        },
        editorMoreFeatures(val, oldVal){
            this.$store
				.dispatch('patchAccount', {
					account: this.account,
					data: {
						editorMoreFeatures: val,
					},
				})
				.then(() => {
					Logger.info('editor more features updated')
				})
				.catch((error) => {
					Logger.error('could not update editor more features', { error })
					this.editorMoreFeatures = oldVal
					throw error
				})
        },
    }
}
</script>
