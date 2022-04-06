import {useContext, useState} from "react";
import setupContext from '../data/setup-context'
import classNames from "classnames";

const {__} = wp.i18n;

export const Step4 = function () {
	const setup = useContext(setupContext)
	let [isCharge, setIsCharge] = useState(setup.data.charge)
	let [chargeMethod, setChargeMethod] = useState(setup.data.charge_method)

	return (
		<>
			<div className="mx-auto mt-32 md:w-1/2 md:h-5/6">
				<h4 className="mx-auto text-2xl md:text-3xl font-medium mb-10">
					{__('Do you want to charge for your courses?', 'learndash')}
				</h4>

				<div className="radio-button-group">
					<input checked={isCharge === "yes"} onChange={(e) => {
						setIsCharge(e.currentTarget.value)
					}} type="radio" id="charge_yes" name="is_charge" value="yes"/>
					<label className="action-button radio white extra-large rounded-tr-none rounded-br-none" htmlFor="charge_yes">
						{__('Yes', 'learndash')}
					</label>

					<input checked={isCharge === "no"} onChange={(e) => {
						setIsCharge(e.currentTarget.value);
						setChargeMethod('');
					}} type="radio" id="charge_no" name="use_the_email" value="no"/>
					<label className="action-button radio white extra-large rounded-tl-none rounded-bl-none" htmlFor="charge_no">
						{__('No', 'learndash')}
					</label>
				</div>

				<div className={isCharge === "no" ? 'invisible' : ''}>
					<p className="font-normal text-base mt-10 mb-4">
						{__('How would you like to accept payments?', 'learndash')}
						<span className="font-light pl-1">
							{__('Choose one.', 'learndash')}
						</span>
					</p>

					<div
						className={classNames("radio-button-group mx-auto payment flex space-x-6 -ml-6", {
							'items-start': chargeMethod.length > 0
						})}
					>
						<input type="radio" checked={chargeMethod === "stripe"} onChange={(e) => {
							setChargeMethod(e.currentTarget.value)
						}} id="payment_stripe" name="payment" value="stripe"/>

						<label className="payment-card inline-block action-button white p-10 text-center stripe w-1/2" htmlFor="payment_stripe">
							<span className="h-20 w-20 mb-5 rounded-full bg-gray-icon flex items-center justify-center mr-auto ml-auto">
								<img className="inline-block w-14" src={ldSetupWizard.urls.assets + 'img/stripe.png'} alt=""/>
							</span>

							<span className="font-medium">
								{__('Stripe', 'learndash')}
							</span>

							<p className="font-normal mt-2.5">
								{__('With Stripe charge credit cards and pay low merchant fees.', 'learndash')}
							</p>

							<div className="mt-7">
								{ ! ldSetupWizard.data.stripe_connected
									? (
										<a className="action-button purple large" href={ldSetupWizard.urls.stripe_connect}>
											{__('Connect', 'learndash')}
										</a>
									)
									: (
										<>
											<button className="action-button green large cursor-default">
												{__('Connected', 'learndash')}
											</button>
											{ chargeMethod === "stripe" && (
												<p className="mt-4 font-normal text-left" dangerouslySetInnerHTML={{__html: ldSetupWizard.data.stripe_webhook_notice}}></p>
											)}
										</>
									)
								}
							</div>
						</label>

						<input type="radio" checked={chargeMethod === "woocommerce"} onChange={(e) => {
							setChargeMethod(e.currentTarget.value)
						}} id="payment_woo" name="payment" value="woocommerce"/>

						<label className="payment-card inline-block action-button white p-10 text-center w-1/2" htmlFor="payment_woo">
							<span className="h-20 w-20 mb-5 rounded-full bg-gray-icon flex items-center justify-center mr-auto ml-auto">
								<img className="inline-block w-10 mt-2" src={ldSetupWizard.urls.assets + 'img/woocommerce.png'} alt=""/>
							</span>

							<span className="font-medium">
								{__('WooCommerce', 'learndash')}
							</span>

							<p className="font-normal mt-2.5">
								{__('Use WooCommerce to manage payments, invoices and more.', 'learndash')}
							</p>

							{chargeMethod === "woocommerce" && (
								<p className="text-xs font-normal mt-6">
									<img className="inline-block w-5 h-6 mr-1.5" src={ldSetupWizard.urls.assets + 'img/icon-thumbs-up.png'} alt=""/>

									{__('We\'ll install the plugins you need', 'learndash')}
								</p>
							)}
						</label>
					</div>
				</div>
			</div>

			<div className="flex justify-between mt-16">
				<div>
					<button className="action-link no-underline" onClick={() => {
						setup.updateData({
							...setup.data, scene: 'step-3'
						})
					}}>
						<svg className="w-3 inline-block mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
							<path d="M257.5 445.1l-22.2 22.2c-9.4 9.4-24.6 9.4-33.9 0L7 273c-9.4-9.4-9.4-24.6 0-33.9L201.4 44.7c9.4-9.4 24.6-9.4 33.9 0l22.2 22.2c9.5 9.5 9.3 25-.4 34.3L136.6 216H424c13.3 0 24 10.7 24 24v32c0 13.3-10.7 24-24 24H136.6l120.5 114.8c9.8 9.3 10 24.8.4 34.3z"/>
						</svg>
						<span>{__('Back', 'learndash')}</span>
					</button>
				</div>
				<div>
					<div className="inline-block mr-6">
						<div className="step active">
							<span>1</span>
							{__('Your Info', 'learndash')}
						</div>
					</div>
					<div className="inline-block mr-6">
						<div className="step active">
							<span>2</span>
							{__('Your Courses','learndash')}
						</div>
					</div>
					<div className="inline-block mr-6">
						<div className="step active">
							<span>3</span>
							{__('Payment','learndash')}
						</div>
					</div>
					<div className="inline-block">
						<div className="step">
							<span>4</span>
							{__('Summary','learndash')}
						</div>
					</div>
				</div>
				<div>
					<button
						onClick={() => {
							const data = {
								scene: 'step-5',
								charge: isCharge,
								charge_method: chargeMethod
							};

							setup.updateData({ ...setup.data, ...data });
							setup.saveDataDB(data);
						}}
						disabled={isCharge === "yes" && chargeMethod.length === 0}
						className={
							classNames("action-button", {
								'blue': isCharge === "no" || (isCharge === "yes" && chargeMethod.length > 0)
							})
						}
						type="button"
					>
						{__('Next', 'learndash')}
					</button>
				</div>
			</div>
		</>
	)
}
