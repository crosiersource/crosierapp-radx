import axios from "axios";

export async function fetchTableData({
  apiResource,
  page = 1,
  rows = 10,
  order = [],
  filters = {},
  allRows = false,
  complement = "",
}) {
  const params = {
    headers: {
      "Content-Type": "application/json;charset=UTF-8",
    },
  };
  const queryPage = `?page=${allRows ? 1 : page}`;
  const queryRows = `&rows=${allRows ? Number.MAX_SAFE_INTEGER : rows}`;
  let queryOrder = [];
  let queryFilter = "";

  order?.forEach((value, key) => {
    queryOrder += `&order[${key}]=${value}`;
  }, order);

  // eslint-disable-next-line no-restricted-syntax
  for (const key in filters) {
    if (filters[key] !== null && filters[key] !== "") {
      if (!Array.isArray(filters[key])) {
        queryFilter += `&${key}=${filters[key]}`;
      } else {
        // eslint-disable-next-line no-loop-func
        filters[key].forEach(function iterate(item) {
          queryFilter += `&${key}[]=${item}`;
        });
      }
    }
  }

  return axios.get(
    `${apiResource}${queryPage}${queryRows}${queryFilter}${queryOrder}${complement}`,
    params
  );
}
