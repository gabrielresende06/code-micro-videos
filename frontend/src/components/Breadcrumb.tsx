import React  from 'react';
import {
    Link,
    createStyles,
    makeStyles,
    Theme,
    LinkProps,
    Typography,
    Breadcrumbs, Container, Box,
} from "@material-ui/core";

import { Location } from 'history'
import { Route } from 'react-router'
import { Link as RouterLink } from 'react-router-dom'
import routes from "../routes";
import RouteParser from 'route-parser'

const breadcrumbNameMap: {[key: string]: string} = {}
routes.forEach(route => breadcrumbNameMap[route.path as string] = route.label)

const useStyles = makeStyles((theme: Theme) =>
    createStyles({
        root: {
            display: 'flex',
            flexDirection: 'column',
        },
        linkRouter: {
            color: '#4db5ab',
            "&:focus, &:active": {
                color: '#4db5ab',
            },
            "&:hover": {
                color: '#055a52',
            },
        }
    })
)

interface LinkRouterProps extends LinkProps {
    to: string
    replace?: boolean
}

const LinkRouter = (props: LinkRouterProps) => <Link {...props} component={RouterLink as any} />

const Breadcrumb: React.FC = () => {
    const classes = useStyles()

    const makeBreadcrumb = (location: Location) => {
        const pathNames = location.pathname.split('/').filter(x => x)
        pathNames.unshift('/')

        return (
            <Breadcrumbs aria-label={'breadcrumb'}>
                {pathNames.map((value, index) => {
                    const last = index === pathNames.length - 1
                    const to = `${pathNames.slice(0, index + 1).join('/').replace('//', '/')}`
                    const route = Object.keys(breadcrumbNameMap).find(path => new RouteParser(path).match(to))

                    if (route === undefined) {
                        return false;
                    }

                    return last ? (
                        <Typography color={'textPrimary'} key={to}>{breadcrumbNameMap[route]}</Typography>
                    ) : (
                        <LinkRouter color={'inherit'} to={to} key={to} className={classes.linkRouter}>
                            {breadcrumbNameMap[route]}
                        </LinkRouter>
                    )
                })}
            </Breadcrumbs>
        )
    }

    return (
        <Container>
            <Box paddingBottom={2}>
                <Route>
                    {({location}) => makeBreadcrumb(location)}
                </Route>
            </Box>
        </Container>
    )
}

export default Breadcrumb
