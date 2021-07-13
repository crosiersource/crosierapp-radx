import axios from "axios";

export async function deleteEntityData(apiResource) {
  return axios.delete(apiResource, {
    headers: {
      "Content-Type": "application/ld+json",
    },
    validateStatus(status) {
      return status < 500;
    },
  });
}
