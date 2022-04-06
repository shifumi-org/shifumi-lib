import {createContext} from "react";

export const setupData = ldSetupWizard.data

const SetupContext = createContext({})
export const SetupProvider = SetupContext.Provider
export default SetupContext;
