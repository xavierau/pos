export const PurchaseEvent = {
    EditItem: 'Purchase:Item:Edit',
    DeleteItem: 'Purchase:Item:Delete',
    IncrementItem: 'Purchase:Item:Increment',
    DecrementItem: 'Purchase:Item:Decrement',
    VerifyItemQty: 'Purchase:Item:VerifyQty',
    SubmitUpdateItemForm: 'Purchase:Item:SubmitUpdateForm',
}

export const PromotionEvent = {
    DeleteItem: 'Promotion:Item:Delete',
}

export const PurchasePaymentEvent = {
    Pdf: 'Purchase:Payment:Pdf',
    EditPayment: 'Purchase:Payment:Edit',
    SendPaymentEmail: 'Purchase:Payment:SendEmail',
    SendPaymentSMS: 'Purchase:Payment:SendSMS',
    RemovePayment: 'Purchase:Payment:RemovePayment',
}

export const PosEvents = {
    CreateCustomer: 'POS:Customer:Create',
    SelectProduct: 'POS:Product:Select',
    UpdateDetail: 'POS:Detail:Update',
    IncrementDetail: 'POS:Detail:Increment',
    DecrementDetail: 'POS:Detail:Decrement',
    VerifyQty: 'POS:Detail:VerifyQty',
    DeleteDetail: 'POS:Detail:Delete',
    SearchProduct: 'POS:Product:Search',
    SubmitPayment: 'POS:Payment:Submit',
}
