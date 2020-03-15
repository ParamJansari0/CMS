import React from "react";
import { BrowserRouter, Switch, Route } from "react-router-dom";
import landingPage from "./landingPage";
import getStarted from "./getStarted";
import tempPage from "../tempPage";
import loginpage from "./loginpage";
import passwordsubmit from "./passwordsubmit";
import submitemail from "./submitemail";
import securityCode from "./securityCode";
import createAccount from "./createAccount";

const Main = () => (
  <BrowserRouter>
    <Switch>
      <Route exact path="/" component={landingPage} />
      <Route path="/loginpage" component={loginpage} />
      <Route path="/getStarted" component={getStarted} />
      <Route path="/tempPage" component={tempPage} />
      <Route path="/passwordsubmit" component={passwordsubmit} />
      <Route path="/submitemail" component={submitemail} />
      <Route path="/securityCode" component={securityCode} />
      <Route path="/createAccount" component={createAccount} />
    </Switch>
  </BrowserRouter>
);

export default Main;
