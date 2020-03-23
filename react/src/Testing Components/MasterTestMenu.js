import React, { Component } from "react";
import Home from '../HomePage';
import Editor from '../editor/Editor';
import Dashboard from '../dashboard/Dashboard'
import SitePage from '../Site Page/SitePage';


class MasterTestMenu extends Component {
  constructor(props) {
    super(props);

    this.state = {
      viewPage: ""
    };

    this.handleEditorClick = this.handleEditorClick.bind(this);
    this.handleHomeClick = this.handleHomeClick.bind(this);
    this.handleDashClick = this.handleDashClick.bind(this);
    this.handleSitePageClick = this.handleSitePageClick.bind(this);
  }

  handleEditorClick() {
    this.setState({ viewPage: "Editor" });
  }

  handleHomeClick() {
    this.setState({ viewPage: "Home" });
  }

  handleDashClick() {
    this.setState({ viewPage: "Dash" });
  }

  handleSitePageClick() {
    this.setState({ viewPage: "SitePage" });
  }


  render() {
    const view = this.state.viewPage;
    let page;
    switch (view) {
      case "Editor":
        page = <Editor />;
        break;
      case "Home":
        page = <Home />;
        break;
      case "Dash": 
        page = <Dashboard />;
        break;
      case "SitePage":
        page = <SitePage onClick={this.handleDashClick}/>
        break;
      default:
        page = <Home />;
    }
    return (
      <div>
        <div>
          <button onClick={this.handleEditorClick}>Editor</button>
          <button onClick={this.handleHomeClick}>Home</button>
          <button onClick={this.handleDashClick}>Dash</button>
          <button onClick={this.handleSitePageClick}>Site Page</button>

        </div>
        <div id="testingCanvas">
          {page}
        </div>
      </div>


    );
  }
}
export default MasterTestMenu;
