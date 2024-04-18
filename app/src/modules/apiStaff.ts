/**
 * Fonctions pour dialoguer avec le serveur
 * @since 03/08/2022 - Version initiale
 */

export type data_msg = {
  nom: string;
  mail: string;
  message: string;
  sujet?: string;
  tel?: string;
};

/**
 * Send a POST request with JSON data to the specified URL.
 * @returns A Promise that resolves to a string representing the API response.
 */
export async function postMsg(
  message: data_msg,
  url = "/api/msg"
): Promise<boolean> {
  const Response = await fetch(url, {
    method: "POST",
    headers: {
      "Content-Type": "application/json;charset=utf-8",
    },
    body: JSON.stringify(message),
  });

  if (Response.ok && Response.status == 201) {
    return true;
  }

  return false;
}
