import React, {useState} from 'react';
import { IconButton, MenuItem, Menu as UiMenu } from "@material-ui/core";
import MenuIcon from "@material-ui/icons/Menu";
import routes, {MyRouteProps} from "../../routes";
import {Link} from "react-router-dom";

const listRoutes = ['dashboard', 'categories.list', 'genres.list', 'members.list'];
const menuRoutes = routes.filter(route => listRoutes.includes(route.name))

const Menu: React.FC = () => {
    const [anchorEl, setAnchorEl] = useState(null)
    const open = Boolean(anchorEl)

    const handleOpen = (event: any) => setAnchorEl(event.currentTarget)
    const handleClose = () => setAnchorEl(null)

    return (
        <React.Fragment>
            <IconButton
                color={'inherit'}
                edge={'start'}
                aria-label={'open drawer'}
                aria-controls={'menu-appbar'}
                aria-haspopup={'true'} onClick={handleOpen}>
                <MenuIcon />
            </IconButton>
            <UiMenu id={'menu-appbar'}
                  anchorEl={anchorEl}
                  open={open}
                  onClose={handleClose}
                  anchorOrigin={{ vertical: 'bottom', horizontal: 'center'}}
                  transformOrigin={{ vertical: 'top', horizontal: 'center'}}
                  getContentAnchorEl={null}
            >
                {
                    listRoutes.map((routeName, key) => {
                        const route = menuRoutes.find(route => route.name === routeName) as MyRouteProps
                        return (
                            <MenuItem key={key}
                                      component={Link}
                                      onClick={handleClose}
                                      to={route.path as string}
                            >
                                {route.label}
                            </MenuItem>
                        )
                    })
                }

            </UiMenu>
        </React.Fragment>
    );
};

export default Menu
