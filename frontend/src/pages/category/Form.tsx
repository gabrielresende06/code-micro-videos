import React from 'react';
import {Box, Button, ButtonProps, Checkbox, makeStyles, TextField, Theme} from "@material-ui/core";
import {useForm, Controller} from "react-hook-form";
import categoryHttp from "../../util/http/category.http";

const useStyles = makeStyles((theme: Theme) => ({
    submit: {
        margin: theme.spacing(1)
    }
}))

const Form: React.FC = () => {
    const classes = useStyles()
    const buttonProps: ButtonProps = {
        variant: 'outlined',
        className: classes.submit,
    }

    const { register, handleSubmit, control, getValues } = useForm()
    const onSubmit = (data, event) => {

        categoryHttp.create(data).then(response => console.log(response))
    }

    return (
        <form onSubmit={handleSubmit(onSubmit)}>
            <TextField label={'Nome'} {...register('name', { required: true })} fullWidth variant={'outlined'} />
            <TextField {...register("description", { required: true })}
                       label={'Descrição'}
                       multiline rows={4} fullWidth
                       variant={'outlined'} margin={'normal'} />
            <Controller
                name="is_active"
                control={control}
                defaultValue={true}
                render={({ field }) => <Checkbox {...field} defaultChecked />}
            />Ativo?
            <Box dir={'rtl'}>
                <Button {...buttonProps} onClick={() => onSubmit(getValues(), null)}>Salvar</Button>
                <Button {...buttonProps} type={'submit'}>Salvar e continuar editando</Button>
            </Box>
        </form>
    )
}

export default Form
