const jqFormError = (formId, errors = {}) => {
	if (errors) {
		const formElement = $(`#${formId}`);
		const fieldMessages = formElement.find("[data-field]");
		fieldMessages.each(function (_, element) {
			const error = errors[$(element).data("field")];
			$(element).html(error || "");
		});
	}
};

const jqFormSubmit = (
	formId,
	{ success, beforeSend, complete, error } = {},
	config = {}
) => {
	const formElement = $(`#${formId}`);
	if (formElement.length) {
		const data = new FormData(formElement[0]);
		const btnSubmit = formElement?.find("[type=submit]");
		return $.ajax({
			type: formElement?.attr("method") || "GET",
			url: formElement?.attr("action") || "",
			data,
			dataType: "json",
			processData: false,
			contentType: false,
			...(typeof config == "object" && config),
			beforeSend: (res, req) => {
				btnSubmit?.attr("disabled", true);
				if (typeof beforeSend === "function") {
					beforeSend(res, req);
				}
			},
			complete: (res, status) => {
				const { responseJSON: data } = res;
				btnSubmit?.attr("disabled", false);
				jqFormError(formId, data?.errors);
				if (typeof complete === "function") {
					complete(res, status);
				}
			},
			success: (data, status, res) => {
				if (typeof success === "function") {
					success(data, status, res);
				}
			},
			error: (res, status) => {
				const { responseJSON: data } = res;
				if (typeof error === "function") {
					error(data, status, res);
				}
			},
		});
	}
	throw new Error(`Form element with id '#${formId}' not found`);
};
