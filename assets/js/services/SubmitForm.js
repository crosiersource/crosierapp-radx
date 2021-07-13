import { putEntityData } from "./ApiPutService";
import { postEntityData } from "./ApiPostService";

export async function submitForm({
  apiResource,
  formData,
  schemaValidator,
  setUrlId = true,
  fnBeforeSave = null,
  fnAfterGet = null,
  toast = null,
}) {
  console.log("ini submitForm");
  console.log(formData);
  console.log("^^^^^^^^^^");
  if (schemaValidator) {
    console.log("tem schemaValidator");
    try {
      formData = await schemaValidator.validate(formData, {
        abortEarly: false,
      });
    } catch (err) {
      console.log(err);
      console.log(err?.inner);
      const msgGl = err?.inner || "Erro ao validar dados";
      if (toast) {
        toast.add({
          severity: "error",
          summary: "Erro",
          detail: msgGl,
          life: 3000,
        });
      } else {
        console.error(msgGl);
      }
      if (err?.inner) {
        err.inner?.forEach((element) => {
          if (element?.path) {
            const msg = element.message || "Valor inválido";
            if (toast) {
              toast.add({
                severity: "error",
                summary: "Erro",
                detail: msg,
                life: 3000,
              });
            } else {
              console.error(msg);
            }
          }
        });
      }
    }
  } else {
    console.log("NÃO tem schemaValidator");
  }
  console.log("continuando");
  let response;
  if (fnBeforeSave) {
    console.log("antes do fnBeforeSave");
    fnBeforeSave(formData);
    console.log("depois do fnBeforeSave");
  }
  if (formData["@id"]) {
    try {
      response = await putEntityData(formData["@id"], JSON.stringify(formData));
    } catch (e) {
      console.err("Erro ao efetuar a requisição PUT");
      console.err(e);
    }
  } else {
    try {
      response = await postEntityData(apiResource, JSON.stringify(formData));
    } catch (e) {
      console.err("Erro ao efetuar a requisição POST");
      console.err(e);
    }
  }
  if ([200, 201].includes(response.status)) {
    console.log(`ok, retornando com status ${response.status}`);
    formData = response.data;
    if (fnAfterGet) {
      formData = fnAfterGet(formData);
    }

    if (setUrlId) {
      window.history.pushState("form", "id", `?id=${formData.id}`);
    }
    if (toast) {
      toast.add({
        severity: "success",
        summary: "Sucesso",
        detail: "Registro salvo com sucesso",
        life: 3000,
      });
    }
    console.log("retornando data: ");
    console.log(formData);
    return formData;
  }
  // if (response.status >= 400 && response.status < 500) {
  console.log("erro entre 400 e 500");
  console.log(response);
  const errMsg = response.data["hydra:description"] || "Ocorreu um erro ao salvar!";
  // }

  // else...
  console.error("Ocorreu um erro salvar!");

  if (toast) {
    toast.add({
      severity: "error",
      summary: "Erro",
      detail: errMsg,
      life: 5000,
    });
  }
  return false;
}
