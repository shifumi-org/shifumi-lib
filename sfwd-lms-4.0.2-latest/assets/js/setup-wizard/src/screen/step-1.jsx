import {useContext} from "react";
import setupContext from '../data/setup-context'

const {__} = wp.i18n;
export const Step1 = function () {
	const setup = useContext(setupContext)
	return (
		<>
			<div className="mx-auto md:w-2/4 text-center flex flex-col justify-center">
				<div>
					<img
						className="inline-block mb-8"
						width="80"
						src={ldSetupWizard.urls.assets + 'img/party-popper.png'}
						alt=""
					/>

					<h3 className="text-2xl md:text-5xl font-medium mb-8">
						{__('Welcome to LearnDash!', 'learndash')}
					</h3>

					<p className="md:w-3/4 inline-block m-auto text-sm">
						{__('LearnDash is The #1 WordPress LMS Plugin. Trusted by Fortune 500s & Universities.', 'learndash')}
					</p>
				</div>
			</div>

			<div className="learndash-footer flex justify-end">
				<div>
					<a className="action-link" href={ldSetupWizard.urls.dismiss}>
						{__('Dismiss Setup Wizard', 'learndash')}
					</a>

					<button onClick={() => {
						setup.updateData({
							...setup.data, scene: 'step-2'
						})
					}} className="action-button blue" type="button">
						{__('Get Started', 'learndash')}
					</button>
				</div>
			</div>
		</>
	)
}
