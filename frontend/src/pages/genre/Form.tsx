import * as React from 'react';
import {
    Box, Button,
    ButtonProps,
    makeStyles, MenuItem,
    TextField,
    Theme
} from "@material-ui/core";
import {useForm} from "react-hook-form";
import {useEffect, useState} from "react";
import genreHttp from '../../util/http/genre.http'
import categoryHttp from '../../util/http/category.http'
import {Category} from '../../util/models/models'

const useStyles = makeStyles((theme: Theme) => {
    return {
        submit: {
            margin: theme.spacing(1)
        }
    }
})

const Form = () => {
    const classes = useStyles();

    const buttonProps: ButtonProps = {
        className: classes.submit,
        variant: "outlined"
    }

    const [categories, setCategories] = useState<Category[]>([])
    const { register, handleSubmit, getValues, setValue, watch } =
        useForm<{ name, categories_id }>({
        defaultValues: {categories_id: []}
    });

    useEffect(() => {
        // register("categories_id")
    }, [register])

    useEffect(() => {
        categoryHttp.list().then(({data}) => setCategories(data.data ?? []))
    }, [])

    function onSubmit(formData, event) {
        console.log(event, formData)
        genreHttp.create(formData).then((response) => console.log(response))
    }

    return (
        <form onSubmit={handleSubmit(onSubmit)}>
            <TextField label={"Nome"} fullWidth variant={"outlined"} {...register('name')} />
            {JSON.stringify(getValues())}
            <TextField
                select
                name={"categories_id"}
                value={watch("categories_id")}
                label={"Categorias"}
                margin={"normal"}
                variant={"outlined"}
                fullWidth
                defaultValue={[]}
                onChange={(e) => {
                    setValue('categories_id', e.target.value)
                }}
                SelectProps={{
                    multiple: true
                }}
            >
                <MenuItem value={""}><em>Selecione categorias</em></MenuItem>
                {
                    categories.map((category, key) => (
                        <MenuItem key={key} value={category.id}>{category.name}</MenuItem>
                    ))
                }
            </TextField>
            <Box dir={"rtl"}>
                <Button {...buttonProps} onClick={() => onSubmit(getValues(), null)}>Salvar</Button>
                <Button {...buttonProps} type={"submit"}>Salvar e continuar editando</Button>
            </Box>
        </form>
    )
}

export default Form
