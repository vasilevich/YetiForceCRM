{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{assign var="FIELD_INFO" value=\App\Purifier::encodeHtml(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
	{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
	<div class="form-inline tpl-Edit-Field-MultiEmail js-multi-email">
		<input type="hidden" name="{$FIELD_MODEL->getFieldName()}" value=""/>
		<button class="btn btn-success mr-2 mb-1" type="button" id="button-addon1">
			<span class="fas fa-plus"></span>
		</button>
		<div class="form-group mr-1 mb-1 js-multi-email-row">
			<label for="staticEmail2" class="sr-only">
				{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}
			</label>
			<div class="input-group">
				<div class="input-group-prepend">
					<button class="btn btn-outline-danger border" type="button" id="button-addon1">
						<span class="fas fa-times"></span>
					</button>
				</div>
				<input type="text" class="form-control"
					   name="{$FIELD_MODEL->getFieldName()}_tmp"
					   placeholder="{\App\Language::translate('LBL_EMAIL_ADRESS', $MODULE)}"
					   data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_MultiEmail_Validator_Js.invokeValidation]]"
					   aria-label="Recipient's username with two button addons"
					   aria-describedby="button-addon4"/>
				<div class="input-group-append btn-group-toggle" id="button-addon4"
					 data-toggle="buttons">
					<label class="btn btn-outline-primary border">
						<div class="c-float-label__container">
							<div class="c-float-label__hidden-ph">
								Opted out
							</div>
							<input id="Opted out" type="checkbox" autocomplete="off">
							<span class="far fa-square position-absolute"></span>
							<label class="c-float-label__label" for="Opted out">
								Opted out
							</label>
						</div>
					</label>
				</div>
			</div>
		</div>
	</div>
{/strip}
