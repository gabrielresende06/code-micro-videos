import { RouteProps} from 'react-router-dom'
import Dashboard from "../pages/Dashboard";
import CategoryList from "../pages/category/PageList";
import CategoryPageForm from "../pages/category/PageForm";
import GenreList from "../pages/genre/PageList";
import GenrePageForm from "../pages/genre/PageForm";
import MembersList from "../pages/members/PageList";
import GenreMembersForm from "../pages/members/PageForm";

export interface MyRouteProps extends RouteProps {
    label: string
    name: string
}

const routes: MyRouteProps[] = [
    {
        name: 'dashboard',
        label: 'Dashboard',
        path: '/',
        component: Dashboard,
        exact: true
    },
    {
        name: 'categories.list',
        label: 'Listar categorias',
        path: '/categories',
        component: CategoryList,
        exact: true
    },
    {
        name: 'categories.create',
        label: 'Criar categorias',
        path: '/categories/create',
        component: CategoryPageForm,
        exact: true
    },
    {
        name: 'categories.edit',
        label: 'Editar categorias',
        path: '/categories/:id/edit',
        component: CategoryList,
        exact: true
    },
    {
        name: 'genres.list',
        label: 'Listar gêneros',
        path: '/genres',
        component: GenreList,
        exact: true
    },
    {
        name: 'genres.create',
        label: 'Criar gêneros',
        path: '/genres/create',
        component: GenrePageForm,
        exact: true
    },
    {
        name: 'genres.edit',
        label: 'Editar gêneros',
        path: '/genres/:id/edit',
        component: GenreList,
        exact: true
    },
    {
        name: 'members.list',
        label: 'Listar membros de elenco',
        path: '/members',
        component: MembersList,
        exact: true
    },
    {
        name: 'members.create',
        label: 'Criar membros de elenco',
        path: '/members/create',
        component: GenreMembersForm,
        exact: true
    },
    {
        name: 'members.edit',
        label: 'Editar membros de elenco',
        path: '/members/:id/edit',
        component: MembersList,
        exact: true
    },
];

export default routes;
