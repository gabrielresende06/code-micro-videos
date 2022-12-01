import React, {useEffect, useState} from 'react';
import MUIDataTable, {MUIDataTableColumn} from "mui-datatables";
import {httpVideo} from "../../util/http";
import {Chip} from "@material-ui/core";
import { format, parseISO } from 'date-fns'

const columnsDefinition: MUIDataTableColumn[] = [
    {
        name: "name",
        label: "Nome"
    },
    {
        name: "categories",
        label: 'Categorias',
        options: {
            customBodyRender(value, tableMeta, updateValue) {
                return value.map((category: { name: string }) => category.name).join(', ') ?? ""
            }
        }
    },
    {
        name: 'is_active',
        label: 'Ativo?',
        options: {
            customBodyRender(value, tableMeta, updateValue) {
                return <Chip label={value ? 'Sim' : 'Não' } color={value ? 'primary' : 'secondary'} />
            }
        }
    },
    {
        name: 'created_at',
        label: 'Criado em',
        options: {
            customBodyRender(value, tableMeta, updateValue) {
                return <span>{format(parseISO(value), 'dd/MM/yyyy')}</span>
            }
        }
    },
]

const Table: React.FC = () => {

    const  [data, setData] = useState([])
    useEffect(() => {
        httpVideo.get('genres').then(
            response => setData(response.data.data)
        )
    }, [])

    return (
        <MUIDataTable data={data} title={''} columns={columnsDefinition} />
    )
}

export default Table
