import React, {useEffect, useState} from 'react';
import MUIDataTable, { MUIDataTableColumn } from "mui-datatables";
import { Chip } from "@material-ui/core";
import { format, parseISO } from 'date-fns'
import categoryHttp from "../../util/http/category.http";

const columnsDefinition: MUIDataTableColumn[] = [
    {
        name: "name",
        label: "Nome"
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

interface Category {
    id: string
    name: string
}

const Table: React.FC = () => {

    const  [data, setData] = useState<Category[]>([])
    useEffect(() => {
        categoryHttp.list<{ data: Category[] }>().then(({ data }) => setData(data.data))
    }, [])

    return <MUIDataTable data={data} title={''} columns={columnsDefinition} />
}

export default Table
