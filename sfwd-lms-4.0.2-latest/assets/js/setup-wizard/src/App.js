import {Step1} from "./screen/step-1";
import {Step2} from "./screen/step-2";
import {Step3} from "./screen/step-3";
import {Step4} from './screen/step-4';
import {Step5} from './screen/step-5';

import {setupData, SetupProvider} from "./data/setup-context";
import {useState} from "react";
import axios from "axios";
import qs from "qs";
import classNames from "classnames";

const {__} = wp.i18n;

function App() {
    let [data, updateData] = useState(setupData)
    const getScene = () => {
        switch (data.scene) {
            case 'step-1':
                return <Step1/>;
            case 'step-2':
                return <Step2/>
            case 'step-3':
                return <Step3/>
            case 'step-4':
                return <Step4/>
            case 'step-5':
                return <Step5/>
        }
    }

    const groupAccess = [
        {
            label: __('Public', 'learndash'),
            value: 'yes'
        },
        {
            label: __('Private', 'learndash'),
            value: 'no'
        }
    ]

    const groupLeader = [
        {
            label: __('Someone Else', 'learndash'),
            value: 'yes'
        },
        {
            label: __('Private', 'learndash'),
            value: 'no'
        }
    ];

    return (
        <SetupProvider value={{
            data: data,
            updateData: updateData,
            saveDataDB: function (data) {
                axios.post(ajaxurl + '?action=learndash_setup_wizard_save_data', qs.stringify({
                    nonce: ldSetupWizard.nonces.save,
                    data: data
                }))
            },
            groupAccess: groupAccess,
            groupLeader: groupLeader,
            plugins_status: ldSetupWizard.plugins
        }}>
			<img
				className="absolute -top-12 right-0 opacity-25"
				src={ldSetupWizard.urls.assets + 'img/learndash-background-logo.svg'}
				alt=""
			/>

            <div className="min-h-screen w-full bg-base-white pb-5">
                <div
					className={
						classNames("container p-5 mx-auto flex flex-col justify-between text-base-normal", {
							'min-h-screen': 'step-1' === data.scene
						})
					}
				>
					<div className="text-center md:text-left">
                        <a className="inline-block" target="_blank" href="https://www.learndash.com/">
                            <img src={ldSetupWizard.urls.assets + 'img/learndash-logo.svg'} width="145"
                                 alt="LearnDash"/>
                        </a>
                    </div>

                    {getScene()}
                </div>
            </div>
        </SetupProvider>
    );
}

export default App;
