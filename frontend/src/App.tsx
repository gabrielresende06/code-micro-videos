import AppRouter from "./routes/AppRouter";
import { Navbar } from "./components/Navbar";

import React from "react";
import { Box } from "@material-ui/core";
import {BrowserRouter} from "react-router-dom";
import Breadcrumb from "./components/Breadcrumb";


const App: React.FC = () => {
  return (
    <React.Fragment>
        <BrowserRouter>
            <Navbar />
            <Box paddingTop={'70px'}>
                <Breadcrumb />
                <AppRouter />
            </Box>
        </BrowserRouter>
    </React.Fragment>
  );
}

export default App;
