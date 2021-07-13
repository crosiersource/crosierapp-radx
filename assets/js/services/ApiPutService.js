import axios from "axios";

export async function putEntityData(apiResource, data) {
  const params = {
    headers: {
      "Content-Type": "application/ld+json",
    },
    validateStatus(status) {
      return status < 500; // Resolve only if the status code is less than 500
    },
  };

  return axios.put(`${apiResource}`, data, params);
}
