import {useContext, useState} from "react";
import setupContext from '../data/setup-context'
import axios from "axios";
import qs from "qs";

const {__} = wp.i18n;

export const Step5 = function () {
	const setup = useContext(setupContext)

	const [CBInstall, setCBInstall] = useState(setup.data.course_type.includes("certificate") || setup.data.course_type.includes("timed") || setup.data.course_type.includes("group_courses"))
	const [CGInstall, setCGInstall] = useState(setup.data.course_amount === "multiple" || setup.data.course_type.includes("certificate") || setup.data.course_type.includes("timed") || setup.data.course_type.includes("group_courses"))
	const [WCInstall, setWCInstall] = useState((setup.data.charge === "yes" && setup.data.charge_method === "woocommerce"))

	const [showAllSettings, setShowAllSettings] = useState(false)
	const [groupArchivePage, setGroupArchivePage] = useState('yes' === setup.data.group_access)
	const [publicGroup, setPublicGroup] = useState('yes' === setup.data.group_access)
	const [manageUser, setManageUser] = useState('yes' === setup.data.group_leader)
	const [groupAutoEnroll, setGroupAutoEnroll] = useState('yes' === setup.data.group_leader)
	const [courseAutoEnroll, setCourseAutoEnroll] = useState('yes' === setup.data.group_leader)
	const [bypassCourseLimit, setByPassCourseLimit] = useState('yes' === setup.data.group_leader)

	// Because we will download and install plugins, it will make the request take long and can be timeout, better to
	// separate it to many small tasks.
	const steps = [
		'create_registration_pages',
		'process_course_listing',
		'process_certificate_builder',
		'process_woo',
		'update_settings'
	];

	let step = 0;
	const [loading, setLoading] = useState(false)

	const finalize = function () {
		setLoading(true)
		let curr_step = steps[step]
		if (undefined === curr_step) {
			return;
		}
		axios.post(ajaxurl + '?action=learndash_finalize', qs.stringify({
			nonce: ldSetupWizard.nonces.finalize,
			data: {
				...setup.data, ...{
					step: steps[step],
					group_archive_page: groupArchivePage,
					public_group: publicGroup,
					manage_user: manageUser,
					group_auto_enroll: groupAutoEnroll,
					course_auto_enroll: courseAutoEnroll,
					bypass_course_limit: bypassCourseLimit,
					certificate_builder: CBInstall,
					course_grid: CGInstall,
					woocommerce: WCInstall
				}
			}
		})).then((res) => {
			if (res.data.data.completed === false) {
				step += 1;
				finalize();
			}else{
				window.location = res.data.data.redirect;
			}
		})
	}

	return (
		<>
			<div className="mx-auto my-14 lg:w-5/12 pb-16">
				<h4 className="text-3xl font-medium mb-8">
					{__('Here\'s what we\'ll setup for you', 'learndash')}
				</h4>

				<h5 className="text-base font-medium">
					{__('We will create the following pages for you:', 'learndash')}
				</h5>

				<div className="flex items-center mt-4 overflow-hidden">
					<span className="h-14 w-14 min-w-14 float-left rounded-full mr-4 bg-gray-icon flex items-center justify-center">
						<img className="inline-block w-6" src={ldSetupWizard.urls.assets + 'img/icon-registration.png'} alt=""/>
					</span>

					<div>
						<strong className="font-medium text-sm">
							{__('Registration & Registration Success', 'learndash')}
						</strong>
						<p className="text-sm font-light pt-1 pr-6">
							{__('Allow students to register for your courses and confirm successful registrations.', 'learndash')}
						</p>
					</div>
				</div>

				{setup.data.courses_amount === 'multiple' && (
					<div className="flex items-center mt-4 overflow-hidden">
						<span className="h-14 w-14 min-w-14 float-left rounded-full mr-4 bg-gray-icon flex items-center justify-center">
							<img className="inline-block w-6" src={ldSetupWizard.urls.assets + 'img/icon-courses-listing.png'} alt=""/>
						</span>

						<div>
							<strong className="font-medium text-sm">
								{__('Courses Listing', 'learndash')}
							</strong>
							<p className="text-sm font-light pt-1 pr-6">
								{__('Display all available courses.', 'learndash')}
							</p>
						</div>
					</div>
				)}

				{setup.data.course_type.includes("group_courses") && (
					<>
						<h5 className="text-base font-medium mt-8 mb-2">
							{__('We will enable the following group settings:', 'learndash')}
						</h5>

						<p className="text-xxs">
							{__('These settings can be changed at any time in learnDash LMS > Groups > Settings', 'learndash')}
						</p>

						{publicGroup && (
							<div className="flex items-center mt-4 overflow-hidden">
								<span className="h-14 w-14 min-w-14 float-left rounded-full mr-4 bg-gray-icon flex items-center justify-center">
									<img className="inline-block w-6" src={ldSetupWizard.urls.assets + 'img/icon-lock.png'} alt=""/>
								</span>

								<div>
									<strong className="font-medium text-sm">
										{__('Groups will be made public on your site', 'learndash')}
									</strong>
									<p className="text-sm font-light pt-1 pr-6">
										{__('You can always change this later if you decide.', 'learndash')}
									</p>
								</div>
							</div>
						)}

						{manageUser && (
							<div className="flex items-center mt-4 overflow-hidden">
								<span className="h-14 w-14 min-w-14 float-left rounded-full mr-4 bg-gray-icon flex items-center justify-center">
									<img className="inline-block w-6" src={ldSetupWizard.urls.assets + 'img/icon-group-leaders.png'} alt=""/>
								</span>

								<div>
									<strong className="font-medium text-sm">
										{__('Group leaders will be enabled on your site', 'learndash')}
									</strong>
									<p className="text-sm font-light pt-1 pr-6">
										{__('They\'ll be able to create and manage users, enroll in managed groups, and have access to all content in any order.','learndash')}
									</p>
								</div>
							</div>
						)}

						{!showAllSettings && (
							<div className="mt-4 ml-16 pl-2 overflow-hidden">
								<span
									className="underline text-sm font-light text-blue-link cursor-pointer"
									onClick={() => setShowAllSettings(true)}
								>
									{__('+ Show all settings being updated', 'learndash')}
								</span>
							</div>
						)}

						{showAllSettings && (
							<div className="rounded border border-gray-light px-7 py-4 mt-7 -mx-10">
								<div className="mb-4 overflow-hidden">
									<div className="flex justify-between">
										<div>
											<strong className="font-medium text-base block mb-1.5">
												{__('Public Group','learndash')}
											</strong>
											<p className="text-sm font-light float-left pr-10">
												{__('Groups will be made public on your site.','learndash')}
											</p>
										</div>
										<div className="flex items-center justify-center mr-2">
											<label
												htmlFor="public-group"
												className="flex items-center cursor-pointer"
											>
												<div className="relative">
													<input id="public-group" checked={publicGroup} onChange={(e) => {
														setPublicGroup(e.target.checked)
													}} type="checkbox" className="sr-only toggle"/>
													<div className="w-9 h-3.5 bg-gray-light rounded-full shadow-inner"></div>
													<div className="dot absolute w-5 h-5 bg-base-white rounded-full shadow -left-0 -top-1 transition"></div>
												</div>
											</label>
										</div>
									</div>
								</div>

								{publicGroup && (
									<div className="mb-4 overflow-hidden">
										<div className="flex justify-between">
											<div>
												<strong className="font-medium text-base block mb-1.5">
													{__('Group Archive Page', 'learndash')}
												</strong>
												<p className="text-sm font-light float-left pr-10">
													{__('Enables the front end archive page where all groups are listed.', 'learndash')}
												</p>
											</div>
											<div className="flex items-center justify-center mr-2">
												<label
													htmlFor="group-archive-page"
													className="flex items-center cursor-pointer"
												>
													<div className="relative">
														<input id="group-archive-page" checked={groupArchivePage} onChange={(e) => {
															setGroupArchivePage(e.target.checked)
														}} type="checkbox" className="sr-only toggle"/>
														<div className="w-9 h-3.5 bg-gray-light rounded-full shadow-inner"></div>
														<div className="dot absolute w-5 h-5 bg-base-white rounded-full shadow -left-0 -top-1 transition"></div>
													</div>
												</label>
											</div>
										</div>
									</div>
								)}

								<div className="mb-4 overflow-hidden">
									<div className="flex justify-between">
										<div>
											<strong className="font-medium text-base block mb-1.5">
												{__('Manage Users: Basic','learndash')}
											</strong>
											<p className="text-sm font-light float-left pr-10">
												{__('Allow group leader to create and manage users.','learndash')}
											</p>
										</div>
										<div className="flex items-center justify-center mr-2">
											<label
												htmlFor="manage-users"
												className="flex items-center cursor-pointer"
											>
												<div className="relative">
													<input id="manage-users" checked={manageUser} onChange={(e) => {
														setManageUser(e.target.checked)
													}} type="checkbox" className="sr-only toggle"/>
													<div className="w-9 h-3.5 bg-gray-light rounded-full shadow-inner"></div>
													<div className="dot absolute w-5 h-5 bg-base-white rounded-full shadow -left-0 -top-1 transition"></div>
												</div>
											</label>
										</div>
									</div>
								</div>
								<div className="mb-4 overflow-hidden">
									<div className="flex justify-between">
										<div>
											<strong className="font-medium text-base block mb-1.5">
												{__('Managed groups auto-enrollment','learndash')}
											</strong>
											<p className="text-sm font-light float-left pr-10">
												{__('Group Leader will be enrolled in managed groups.','learndash')}
											</p>
										</div>
										<div className="flex items-center justify-center mr-2">
											<label
												htmlFor="group-autoenrollment"
												className="flex items-center cursor-pointer"
											>
												<div className="relative">
													<input id="group-autoenrollment" checked={groupAutoEnroll} onChange={(e) => {
														setGroupAutoEnroll(e.target.checked)
													}} type="checkbox" className="sr-only toggle"/>
													<div className="w-9 h-3.5 bg-gray-light rounded-full shadow-inner"></div>
													<div className="dot absolute w-5 h-5 bg-base-white rounded-full shadow -left-0 -top-1 transition"></div>
												</div>
											</label>
										</div>
									</div>
								</div>
								<div className="mb-4 overflow-hidden">
									<div className="flex justify-between">
										<div>
											<strong className="font-medium text-base block mb-1.5">
												{__('Course Auto-enrollment', 'learndash')}
											</strong>
											<p className="text-sm font-light float-left pr-10">
												{__('Group Leader has access to all courses automatically.', 'learndash')}
											</p>
										</div>
										<div className="flex items-center justify-center mr-2">
											<label
												htmlFor="course-autoenrollment"
												className="flex items-center cursor-pointer"
											>
												<div className="relative">
													<input id="course-autoenrollment" checked={courseAutoEnroll} onChange={(e) => {
														setCourseAutoEnroll(e.target.checked)
													}} type="checkbox" className="sr-only toggle"/>
													<div className="w-9 h-3.5 bg-gray-light rounded-full shadow-inner"></div>
													<div className="dot absolute w-5 h-5 bg-base-white rounded-full shadow -left-0 -top-1 transition"></div>
												</div>
											</label>
										</div>
									</div>
								</div>
								<div className="mb-4 overflow-hidden">
									<div className="flex justify-between">
										<div>
											<strong className="font-medium text-base block mb-1.5">
												{__('Bypass Course Limits', 'learndash')}
											</strong>
											<p className="text-sm font-light float-left pr-10">
												{__('Group Leader can access course content in any order.', 'learndash')}
											</p>
										</div>
										<div className="flex items-center justify-center mr-2">
											<label
												htmlFor="bypass_course_limit"
												className="flex items-center cursor-pointer"
											>
												<div className="relative">
													<input id="bypass_course_limit" checked={bypassCourseLimit} onChange={(e) => {
														setByPassCourseLimit(e.target.checked)
													}} type="checkbox" className="sr-only toggle"/>
													<div className="w-9 h-3.5 bg-gray-light rounded-full shadow-inner"></div>
													<div className="dot absolute w-5 h-5 bg-base-white rounded-full shadow -left-0 -top-1 transition"></div>
												</div>
											</label>
										</div>
									</div>
								</div>
							</div>
						)}
					</>
				)}

				<h5 className="text-base font-medium mt-8">
					{__('We\'ll install the following add-ons for you:', 'learndash')}
				</h5>

				<div className="flex items-center mt-4 overflow-hidden">
					<span className="h-14 w-14 min-w-14 float-left rounded-full mr-4 bg-gray-icon flex items-center justify-center">
						<img className="inline-block w-6" src={ldSetupWizard.urls.assets + 'img/icon-course-certificate.png'} alt=""/>
					</span>

					<div className="flex justify-between w-full">
						<div>
							<strong className="font-medium text-sm block">
								{__('Certificate Builder', 'learndash')}
							</strong>
							<p className="text-sm font-light pt-1 pr-6">
								{__('The easiest way to build certificates for your courses.', 'learndash')}
							</p>
						</div>

						<div className="flex items-center justify-center mr-2">
							<label
								htmlFor="certificate-builder"
								className="flex items-center cursor-pointer"
							>
								<div className="relative">
									{setup.plugins_status.certificate_builder === true
										? (
											<span className="text-xs font-medium rounded-2xl bg-base-silver inline-block -mr-2 py-1.5 px-3">
												{__('Installed', 'learndash')}
											</span>
										)
										: (
											<div className="mr-3">
												<input id="certificate-builder" checked={CBInstall} onChange={(e) => {
													setCBInstall(e.target.checked)
												}} type="checkbox" className="sr-only toggle"/>
												<div className="w-9 h-3.5 bg-gray-light rounded-full shadow-inner"></div>
												<div className="dot absolute w-5 h-5 bg-base-white rounded-full shadow -left-0 -top-1 transition"></div>
											</div>
										)
									}
								</div>
							</label>
						</div>
					</div>
				</div>

				<div className="flex items-center mt-4 overflow-hidden">
					<span className="h-14 w-14 min-w-14 float-left rounded-full mr-4 bg-gray-icon flex items-center justify-center">
						<img className="inline-block w-6" src={ldSetupWizard.urls.assets + 'img/icon-courses-grid.png'} alt=""/>
					</span>

					<div className="flex justify-between w-full">
						<div>
							<strong className="font-medium text-sm block">
								{__('Course Grid', 'learndash')}
							</strong>
							<p className="text-sm font-light pt-1 pr-6">
								{__('Display all available courses in a grid on the Courses page.', 'learndash')}
							</p>
						</div>
						<div className="flex items-center justify-center">
							<label
								htmlFor="course-grid"
								className="flex items-center cursor-pointer mr-2"
							>
								<div className="relative">
									{setup.plugins_status.course_grid === true
										? (
											<span className="text-xs font-medium rounded-2xl bg-base-silver inline-block -mr-2 py-1.5 px-3">
												{__('Installed', 'learndash')}
											</span>
										)
										: (
											<div className="mr-3">
												<input id="course-grid" checked={CGInstall} onChange={(e) => {
													setCGInstall(e.currentTarget.checked)
												}} type="checkbox" className="sr-only toggle"/>
												<div className="w-9 h-3.5 bg-gray-light rounded-full shadow-inner"></div>
												<div className="dot absolute w-5 h-5 bg-base-white rounded-full shadow -left-0 -top-1 transition"></div>
											</div>
										)
									}
								</div>
							</label>
						</div>
					</div>
				</div>

				<div className="flex items-center mt-4 overflow-hidden">
					<span className="h-14 w-14 min-w-14 float-left rounded-full mr-4 bg-gray-icon flex items-center justify-center">
						<img className="inline-block w-7 mt-1" src={ldSetupWizard.urls.assets + 'img/woocommerce.png'} alt=""/>
					</span>

					<div className="flex justify-between w-full">
						<div>
							<strong className="font-medium text-sm block">
								{__('WooCommerce', 'learndash')}
							</strong>
							<p className="text-sm font-light pt-1 pr-6">
								{__('Handle payments, coupons, invoices and more.', 'learndash')}
							</p>
						</div>
						<div className="flex items-center">
							<label
								htmlFor="woocommerce"
								className="flex items-center cursor-pointer mr-2"
							>
								<div className="relative">
									{setup.plugins_status.woocommerce === true
										? (
											<span className="text-xs font-medium rounded-2xl bg-base-silver inline-block -mr-2 py-1.5 px-3">
												{__('Installed', 'learndash')}
											</span>
										)
										: (
											<div className="mr-3">
												<input id="woocommerce" checked={WCInstall} onChange={(e) => {
													setWCInstall(e.currentTarget.checked)
												}} type="checkbox" className="sr-only toggle"/>
												<div className="w-9 h-3.5 bg-gray-light rounded-full shadow-inner"></div>
												<div className="dot absolute w-5 h-5 bg-base-white rounded-full shadow -left-0 -top-1 transition"></div>
											</div>
										)
									}
								</div>
							</label>
						</div>
					</div>
				</div>
			</div>

			<div className="flex justify-between w-full bg-base-white pt-6 pb-10 px-5 fixed bottom-0 left-0">
				<div>
					<button className="action-link no-underline" onClick={() => {
						setup.updateData({
							...setup.data,
							scene: 'step-4'
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
							{__('Your Courses', 'learndash')}
						</div>
					</div>
					<div className="inline-block mr-6">
						<div className="step active">
							<span>3</span>
							{__('Payment', 'learndash')}
						</div>
					</div>
					<div className="inline-block">
						<div className="step active">
							<span>4</span>
							{__('Summary', 'learndash')}
						</div>
					</div>
				</div>

				<div>
					<button onClick={finalize} className="action-button green flex justify-between items-center" type="button">
						<span>Save & Complete</span>
						{loading && (
							<svg className="w-3 ml-3 inline animate-spin" style={{ filter: "invert(100%) brightness(150%)" }} xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
								<path d="M304 48c0 26.51-21.49 48-48 48s-48-21.49-48-48 21.49-48 48-48 48 21.49 48 48zm-48 368c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48-21.49-48-48-48zm208-208c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48-21.49-48-48-48zM96 256c0-26.51-21.49-48-48-48S0 229.49 0 256s21.49 48 48 48 48-21.49 48-48zm12.922 99.078c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48c0-26.509-21.491-48-48-48zm294.156 0c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48c0-26.509-21.49-48-48-48zM108.922 60.922c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48-21.491-48-48-48z"/>
							</svg>
						)}
					</button>
				</div>
			</div>
		</>
	)
}
