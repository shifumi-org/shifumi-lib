import {useContext, useState} from "react";
import setupContext from '../data/setup-context'
import classNames from "classnames";

const {__} = wp.i18n;
export const Step3 = function () {
	const setup = useContext(setupContext)
	let [courseAmount, setCourseAmount] = useState(setup.data.courses_amount)
	let [courseTypes, setCourseTypes] = useState(setup.data.course_type)
	let [groupAccess, setGroupAccess] = useState(setup.data.group_access)
	let [groupLeader, setGroupLeader] = useState(setup.data.group_leader)

	const MyDropdown = (options, selected, callback) => {
		let item = options.find(element => element.value === selected)
		const [value, setValue] = useState(item)
		const [open, setOpen] = useState(false)
		return (
			<>
				<div className="relative inline-block text-left">
					<div>
						<button
							onClick={() => setOpen(!open)}
							type="button" className="action-link text-blue-link font-semibold text-xs" aria-expanded="true" aria-haspopup="true"
						>
							{value.label}
							<svg className="h-3 w-3 inline-block" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
								<path fillRule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clipRule="evenodd"/>
							</svg>
						</button>
					</div>

					<div className={!open ? 'hidden' : 'origin-top-right absolute right-0 mt-2 w-56 z-10 rounded-md shadow-lg bg-base-white focus:outline-none'} role="menu" aria-orientation="vertical" aria-labelledby="menu-button" tabIndex="-1">
						<div className="py-1" role="none">
							{options.map((item, i) => (
								<a
									onClick={(e) => {
										e.preventDefault();

										setValue(item)
										setOpen(false)
										callback(item.value)
									}}
									href="#" key={i} className="block px-4 py-2 text-sm hover:bg-gray-light" role="menuitem" tabIndex="-1"
								>
									{item.label}
								</a>
							))}
						</div>
					</div>
				</div>
			</>
		)
	}

	return (
		<>
			<div className="mx-auto mt-20 lg:w-7/12">
				<h4 className="text-2xl md:text-3xl font-medium mb-4 mt-4 md:mb-8 md:mt-0">
					{__('Tell us about your courses', 'learndash')}
				</h4>

				<div className="md:flex md:justify-between mb-8">
					<p className="font-medium text-base">
						{__('How many courses are you planning to create?','learndash')}
					</p>

					<div className="radio-button-group md:mt-auto md:mb-auto mt-4">
						<input type="radio" id="single_course" onChange={(e) => {
							setCourseAmount(e.currentTarget.value)
						}} checked={courseAmount === "single"} name="courses_amount" value="single"/>
						<label className="action-button radio large white rounded-tr-none rounded-br-none" htmlFor="single_course">
							{__('Just One', 'learndash')}
						</label>

						<input type="radio" id="multi_course" onChange={(e) => {
							setCourseAmount(e.currentTarget.value)
						}} checked={courseAmount === "multiple"} name="courses_amount" value="multiple"/>
						<label className="action-button radio large white rounded-tl-none rounded-bl-none" htmlFor="multi_course">
							{__('Multiple', 'learndash')}
						</label>
					</div>
				</div>

				<p className="font-medium text-base">
					{__('What types of courses will you have?', 'learndash')}
					<span className="font-light pl-1">
						{__('Check all the apply.', 'learndash')}
					</span>
				</p>

				<div className={classNames("mt-4 checkbox-card", { 'selected': courseTypes.includes("single") })}>
					<img
						className="icon-selected"
						src={ldSetupWizard.urls.assets + 'img/icon-course-selected.svg'}
						alt=""
					/>

					<input
						checked={courseTypes.includes("single")}
						onChange={(e) => {
							if (courseTypes.includes("single") === false) {
								setCourseTypes([...courseTypes, 'single'])
							} else {
								setCourseTypes(courseTypes.filter(e => e !== 'single'))
							}
						}}
						className="absolute"
						style={{'clip': 'rect(0,0,0,0)'}}
						value="single" id="simple" name="course_type" type="checkbox"
					/>

					<label htmlFor="simple" className="grid grid-cols-7 rounded-md py-2.5 px-4 border border-solid border-gray-light hover:cursor-pointer hover:border-blue-base">
						<span className="h-16 w-16 rounded-full bg-gray-icon flex items-center mr-3 justify-center">
							<img className="inline-block w-6" style={{ filter: "saturate(40%)" }} src={ldSetupWizard.urls.assets + 'img/icon-course-simple.png'} alt=""/>
						</span>

						<div className="flex flex-col justify-center text-sm font-light col-span-6 pr-5">
							<span className="font-medium block text-sm mb-1.5">
								{__('Simple', 'learndash')}
							</span>

							{__('A video or text based course without certificates or quizzes.', 'learndash')}
						</div>
					</label>
				</div>

				<div className={classNames("mt-3 checkbox-card", { 'selected': courseTypes.includes("certificate") })}>
					<img
						className="icon-selected"
						src={ldSetupWizard.urls.assets + 'img/icon-course-selected.svg'}
						alt=""
					/>

					<input checked={courseTypes.includes("certificate")} onChange={(e) => {
						if (courseTypes.includes("certificate") === false) {
							setCourseTypes([...courseTypes, 'certificate'])
						} else {
							setCourseTypes(courseTypes.filter(e => e !== 'certificate'))
						}
					}} className="absolute" style={{'clip': 'rect(0,0,0,0)'}} id="certificate" value="certificate" name="course_type" type="checkbox"/>

					<label htmlFor="certificate" className="grid grid-cols-7 rounded-md py-2.5 px-4 border border-solid border-gray-light hover:cursor-pointer hover:border-blue-base">
						<span className="h-16 w-16 rounded-full bg-gray-icon flex items-center mr-3 justify-center ">
							<img className="inline-block w-6" src={ldSetupWizard.urls.assets + 'img/icon-course-certificate.png'} alt=""/>
						</span>

						<div className="flex flex-col justify-center text-sm font-light col-span-6 pr-5">
							<span className="font-medium block text-sm mb-1.5">
								{__('Certificate', 'learndash')}
							</span>

							{__('A video or text based course including quizzes and certificates.', 'learndash')}
						</div>
					</label>
				</div>

				<div className={classNames("mt-3 checkbox-card", { 'selected': courseTypes.includes("timed") })}>
					<img
						className="icon-selected"
						src={ldSetupWizard.urls.assets + 'img/icon-course-selected.svg'}
						alt=""
					/>

					<input checked={courseTypes.includes("timed")} onChange={(e) => {
						if (courseTypes.includes("timed") === false) {
							setCourseTypes([...courseTypes, 'timed'])
						} else {
							setCourseTypes(courseTypes.filter(e => e !== 'timed'))
						}
					}} className="absolute" style={{'clip': 'rect(0,0,0,0)'}} id="timed" name="course_type" type="checkbox"/>

					<label htmlFor="timed" className="grid grid-cols-7 rounded-md py-2.5 px-4 border border-solid border-gray-light hover:cursor-pointer hover:border-blue-base">
						<span className="h-16 w-16 rounded-full bg-gray-icon flex items-center mr-3 justify-center ">
							<img className="inline-block w-6" src={ldSetupWizard.urls.assets + 'img/icon-course-timed.png'} alt=""/>
						</span>

						<div className="flex flex-col justify-center text-sm font-light col-span-6 pr-5">
							<span className="font-medium block text-sm mb-1.5">
								{__('Timed', 'learndash')}
							</span>

							{__('A video or text based course including quizzes and certificates.', 'learndash')}
						</div>
					</label>
				</div>

				<div className={classNames("mt-3 checkbox-card", { 'selected': courseTypes.includes("group_courses") })}>
					<img
						className="icon-selected"
						src={ldSetupWizard.urls.assets + 'img/icon-course-selected.svg'}
						alt=""
					/>

					<input checked={courseTypes.includes("group_courses")} onChange={(e) => {
						if (courseTypes.includes("group_courses") === false) {
							setCourseTypes([...courseTypes, 'group_courses'])
						} else {
							setCourseTypes(courseTypes.filter(e => e !== 'group_courses'))
						}
					}} className="absolute" style={{'clip': 'rect(0,0,0,0)'}} id="group_courses" name="course_type" type="checkbox"/>

					<label htmlFor="group_courses" className="grid grid-cols-7 rounded-md py-2.5 px-4 border border-solid border-gray-light hover:cursor-pointer hover:border-blue-base">
						<span className="h-16 w-16 rounded-full bg-gray-icon flex items-center mr-4 justify-center ">
							<img className="inline-block w-6" style={{ filter: "saturate(40%)" }} src={ldSetupWizard.urls.assets + 'img/icon-course-group.png'} alt=""/>
						</span>

						<div className="flex flex-col justify-center text-sm font-light col-span-6 pr-5">
							<span className="font-medium block text-sm mb-1.5">
								{__('Group Courses', 'learndash')}
							</span>

							{__('A course led by group leaders, perfect for cohorts, corporate training and classrooms.', 'learndash')}

							<div className={"text-xs font-normal block mt-5" + (courseTypes.includes("group_courses") ? '' : ' hidden')}>
								<span className="pr-1">
									{__('Are your groups public or private?', 'learndash')}
								</span>

								{MyDropdown(setup.groupAccess, groupAccess, setGroupAccess)}
							</div>

							<div className={"text-xs font-normal block mt-2 mb-6" + (courseTypes.includes("group_courses") ? '' : ' hidden')}>
								<span className="pr-1">
									{__('Who will be leading your group courses?', 'learndash')}
								</span>

								{MyDropdown(setup.groupLeader, groupLeader, setGroupLeader)}
							</div>
						</div>
					</label>
				</div>
			</div>

			<div className="flex justify-between mt-16">
				<div>
					<button className="action-link no-underline" onClick={() => {
						setup.updateData({
							...setup.data, scene: 'step-2'
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
						<div className="step">
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
								scene: 'step-4',
								courses_amount: courseAmount,
								course_type: courseTypes,
								group_access: groupAccess,
								group_leader: groupLeader,
							};

							setup.updateData({ ...setup.data, ...data });
							setup.saveDataDB(data);
						}}
						disabled={courseTypes.length === 0}
						className={
							classNames("action-button", {
								'blue': courseTypes.length > 0
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
