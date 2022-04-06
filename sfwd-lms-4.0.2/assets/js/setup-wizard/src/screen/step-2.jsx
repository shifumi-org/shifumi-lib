import {useContext, useState} from "react";
import setupContext from '../data/setup-context'
import axios from "axios";
import qs from 'qs'
import classNames from "classnames";

const {__} = wp.i18n;

export const Step2 = function () {
	const setup = useContext(setupContext)
	let [email, setEmail] = useState(setup.data.email)
	let [license, setLicense] = useState(setup.data.license_key)
	let [loading, setLoading] = useState(false)
	let [sameEmail, setSameEmail] = useState(setup.data.use_registered_email)
	let [notificationEmail, setNotificationEmail] = useState(setup.data.notification_email)
	let [valid, setValid] = useState('yes' === setup.data.license_validated)
	let [notifValid, setNotifValid] = useState(true)

	let [error, setError] = useState({
		email: '',
		license: ''
	});

	let [notificationEmailError, setNotificationEmailError] = useState('')

	const validateEmail = function () {
		setLoading(true)
		let lerror = {
			email: '',
			license: ''
		}

		if (null === validateEmailFormat(email)) {
			lerror.email = __('Please enter the email address.', 'learndash');
			setValid(false)
		}

		if (undefined === license || license.length === 0) {
			lerror.license = __('Please enter the license key.', 'learndash');
			setValid(false)
		}

		if (lerror.email.length === 0 && lerror.license.length === 0) {
			// trigger a request
			axios.post(ajaxurl + '?action=learndash_setup_wizard_verify_license', qs.stringify({
				nonce: ldSetupWizard.nonces.verify,
				email: email,
				license_key: license
			})).then((response) => {
				if (response.data.success === false) {
					setValid(false)
					setLoading(false)
					lerror = {
						email: '',
						license: __('The email and the license key do not match.', 'learndash')
					}
					setError(lerror)
				} else {
					setValid(true)
					setLoading(false)
					setError({
						email: '',
						license: ''
					})
				}
			})
		} else {
			setLoading(false)
			setError(lerror)
		}
	}

	const validateEmailFormat = (email) => {
		return String(email)
			.toLowerCase()
			.match(
				/^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
			);
	};

	return (
		<>
			<div className="mx-auto mt-20 lg:w-2/5">
				<h4 className="text-3xl font-medium mb-8">
					{__('Let\'s start by getting your info', 'learndash')}
				</h4>

				<div className="mb-4">
					<strong className="font-medium mb-2 inline-block text-base">
						{__('Enter your License', 'learndash')}
					</strong>

					<p className="text-sm">
						{__('Your license details were emailed to you after purchase. You can also find them listed on', 'learndash')}&nbsp;
						<a target="_blank" href={ldSetupWizard.urls.support} className="text-blue-link underline whitespace-nowrap">
							{__('your account', 'learndash')}
						</a>.
					</p>
				</div>

				<div>
					<label className="font-medium mb-2 block text-base" htmlFor="email">
						{__('Email', 'learndash')}
					</label>

					<input
						type="email"
						value={email}
						onChange={(event) => {
							setEmail(event.target.value)
							setValid(false)
						}}
						className="w-full py-2 px-3 text-base outline-0 border border-base-light-gray rounded"
						placeholder={__('Your purchase email', 'learndash')}
					/>

					{error.email.length > 0 ? (
						<span className="text-xs font-semibold text-red-base">{error.email}</span>
					) : ''}
				</div>

				<div className="mb-4 mt-4">
					<label className="font-medium mb-2 block text-base cursor-pointer" htmlFor="email">
						{__('License:', 'learndash')}
					</label>

					<input
						type="text"
						value={license}
						onChange={(event) => {
							setLicense(event.target.value)
							setValid(false)
						}}
						placeholder={__('Your license key', 'learndash')}
						className="w-full py-2 px-3 text-base outline-0 border border-base-light-gray rounded"
					/>

					{error.license.length > 0 ? (
						<span className="text-xs font-semibold text-red-base">{error.license}</span>
					) : ''}

					{valid && (
						<span className="text-xs font-semibold text-green-base">
							{__('Your license is valid.', 'learndash')}
						</span>
					)}
				</div>

				<div className="mb-6 ">
					<button type="button" className="action-button validation-button blue large" onClick={validateEmail}>
						{loading
							?
								<svg className="w-4 inline animate-spin" style={{ filter: "invert(100%) brightness(150%)" }} xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
									<path d="M304 48c0 26.51-21.49 48-48 48s-48-21.49-48-48 21.49-48 48-48 48 21.49 48 48zm-48 368c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48-21.49-48-48-48zm208-208c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48-21.49-48-48-48zM96 256c0-26.51-21.49-48-48-48S0 229.49 0 256s21.49 48 48 48 48-21.49 48-48zm12.922 99.078c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48c0-26.509-21.491-48-48-48zm294.156 0c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48c0-26.509-21.49-48-48-48zM108.922 60.922c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48-21.491-48-48-48z"/>
								</svg>
							: __('Validate', 'learndash')
						}
					</button>
				</div>

				<div className="border-t h-1 border-solid border-gray-light mb-5"></div>

				<div className="grid grid-cols-3 mb-5">
					<div className="col-span-2">
						<h6 className="font-medium mb-2 block text-sm cursor-pointer">
							{__('Communication Preferences', 'learndash')}
						</h6>
						<p className="text-xs">
							{__('Would you like to use the email above for all LearnDash emails and course notifications?', 'learndash')}
						</p>
					</div>
					<div className="radio-button-group mt-auto mb-auto text-right">
						<input
							type="radio"
							id="use_the_email_yes"
							name="use_the_email"
					   		onChange={(e) => {
						   		setSameEmail(e.currentTarget.value)
								setNotifValid(true)
						   	}}
						   	checked={sameEmail === "yes"}
						   	value="yes"
						/>
						<label className="action-button radio white rounded-tr-none rounded-br-none" htmlFor="use_the_email_yes">
							{__('Yes', 'learndash')}
						</label>

						<input
							type="radio"
							id="use_the_email_no"
							name="use_the_email"
							onChange={(e) => {
								setSameEmail(e.currentTarget.value)
								setNotifValid(null !== validateEmailFormat(notificationEmail))
							}}
							checked={sameEmail === "no"}
							value="no"
						/>
						<label className="action-button radio white rounded-tl-none rounded-bl-none" htmlFor="use_the_email_no">
							{__('No', 'learndash')}
						</label>
					</div>
				</div>

				{sameEmail === "no" && (
					<div>
						<label className="text-xs font-semibold cursor-pointer mb-2 inline-block">
							{__('What "From:" email address would you like to use?', 'learndash')}
						</label>

						<input
							type="text"
							value={notificationEmail}
							onChange={(e) => {
								setNotificationEmail(e.target.value)

								if (null === validateEmailFormat(e.target.value)) {
									setNotificationEmailError('Please enter the email address.')
									setNotifValid(false)
								} else {
									setNotificationEmailError('')
									setNotifValid(true)
								}
							}}
							className="w-full py-2 px-3 text-base outline-0 border border-base-light-gray rounded"
						/>

						{notificationEmailError.length > 0 ? (
							<span className="text-xs font-semibold text-red-base">{notificationEmailError}</span>
						) : ''}
					</div>
				)}
			</div>

			<div className="flex justify-between mt-16">
				<div>
					<button className="action-link no-underline" onClick={() => {
						setup.updateData({
							...setup.data, scene: 'step-1'
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
						<div className="step">
							<span>2</span>
							{__('Your Courses', 'learndash')}
						</div>
					</div>
					<div className="inline-block mr-6">
						<div className="step">
							<span>3</span>
							{__('Payment', 'learndash')}
						</div>
					</div>
					<div className="inline-block">
						<div className="step">
							<span>4</span>
							{__('Summary', 'learndash')}
						</div>
					</div>
				</div>
				<div>
					<button
						onClick={() => {
							if (true === valid && true === notifValid) {
								const data = {
									scene: 'step-3',
									email: email,
									license_key: license,
									use_registered_email: sameEmail,
									notification_email: notificationEmail
								};

								setup.updateData({ ...setup.data, ...data });
								setup.saveDataDB(data);
							}
						}}
						disabled={false === valid || false === notifValid}
						className={
							classNames("action-button", {
								'blue': valid && notifValid
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
