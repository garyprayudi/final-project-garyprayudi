<template>
    <div class="d-flex justify-space-between flex-wrap">
        <div>
            <v-btn color="info" style="margin-bottom: 10px;" to="/customers/create" prepend-icon="mdi-plus-thick">
                Add Customer
            </v-btn>
            <v-btn color="success" variant="outlined" style="margin-left: 10px; margin-bottom: 10px;" prepend-icon="mdi-file-export" @click="handleExportXls()">
                Export to Xls
            </v-btn>
            <v-btn color="success" variant="outlined" style="margin-left: 10px; margin-bottom: 10px;" prepend-icon="mdi-file-import" @click="dialog = true">
                Import Customers...
            </v-btn>
        </div>
        <v-card
            color="grey-lighten-3"
        >
            <v-card-text style="width: 400px;">
                <v-text-field
                    density="compact"
                    variant="solo"
                    label="Search Customers..."
                    append-inner-icon="mdi-magnify"
                    single-line
                    hide-details
                    v-on:keyup="handleSearch"
                ></v-text-field>
            </v-card-text>
        </v-card>
    </div>
    <v-table>
        <thead>
            <tr>
                <th class="text-left">Name</th>
                <th class="text-left">Code</th>
                <th class="text-left">City</th>
                <th class="text-left">Address</th>
                <th class="text-left">Email</th>
                <th class="text-left">Birth Date</th>
                <th class="text-left">Mobile Phone</th>
                <th class="text-left">Total Receipts</th>
                <th class="text-left">Actions</th>
            </tr>
        </thead>
        <tbody>
            <tr
                v-for="(customer, index) in customers"
                :key="index"
            >
                <td>{{ customer.customerName }}</td>
                <td>{{ customer.customerCode }}</td>
                <td>{{ customer.customerCity }}</td>
                <td>{{ customer.customerAddress }}</td>
                <td>{{ customer.customerEmail }}</td>
                <td>{{ customer.customerBirthDate }}</td>
                <td>{{ customer.customerNoHandphone }}</td>
                <td>{{ customer.totalReceipts }}</td>
                <td>
                    <v-btn
                        color="warning"
                        icon="mdi-pencil"
                        size="small"
                        @click="$router.push(`/customers/${customer.customerId}/edit`)"
                    />
                    &nbsp;
                    <v-btn
                        color="error"
                        icon="mdi-delete"
                        size="small"
                        @click="handleDestroyCustomer(customer.customerId)"
                    />
                </td>
            </tr>
        </tbody>
    </v-table>
    <v-row justify="center">
        <v-dialog
            v-model="dialog"
            persistent
            width="1024"
        >
            <v-card>
                <v-card-title>
                    <span class="text-h5">Import Customers</span>
                </v-card-title>
                <v-card-text>
                    <v-container>
                        <v-file-input label="File Input" id="import-customer"></v-file-input>
                        <a class="v-btn v-btn--elevated v-theme--light bg-primary v-btn--density-default v-btn--size-default v-btn--variant-elevated ml-10" href="/Template Customers.xlsx">Template Excel</a>
                    </v-container>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn
                        color="blue-darken-1"
                        variant="text"
                        @click="dialog = false"
                    >
                        Close
                    </v-btn>
                    <v-btn
                        color="blue-darken-1"
                        variant="text"
                        @click="handleImport"
                    >
                        Import
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-row>
</template>

<script lang="js">
    import xlsx from 'json-as-xlsx'
    import readXlsxFile from 'read-excel-file'
    import { sendRequest } from '../../utils/request'
    
    export default {
        mounted() {
            this.handleCustomers(`${import.meta.env.VITE_APP_BACKEND_HOST}/api/v1/customers`)
        },
        data: () => ({
            dialog: false,
            customers: []
        }),
        methods: {
            handleCustomers(url = `${import.meta.env.VITE_APP_BACKEND_HOST}/api/v1/customers`) {
                sendRequest('GET', url)
                .then(res => {
                    this.customers = res.data
                })
                .catch(err => {
                    console.log(err)
                })
            },
            handleDestroyCustomer(id) {
                if (confirm("Are you sure ?")) {
                    sendRequest('DELETE', `${import.meta.env.VITE_APP_BACKEND_HOST}/api/v1/customers/${id}`)
                    .then(res => {
                        if (!res.status) {
                            if (!res.status) throw new Error(res.message)
                        }
                        this.handleCustomers()
                        alert('Customer deleted')
                    })
                    .catch(err => {
                        alert(err)
                    })
                }
            },
            handleExportXls() {
                const contents = []
                this.customers?.forEach((customer, i) => {
                    contents.push({
                        name: customer.customerName, 
                        code: customer.customerCode,
                        address: customer.customerAddress,
                        email: customer.customerEmail,
                        birthdate: customer.customerBirthDate,
                        mobile: customer.customerNoHandphone,
                    })
                })

                const exportData = [{
                    sheet: "Customers",
                    columns: [
                        { label: "Name", value: "name" },
                        { label: "Code", value: "code" },
                        { label: "Address", value: "address" },
                        { label: "Email", value: "email" },
                        { label: "Birthdate", value: "birthdate" },
                        { label: "Mobile Phone", value: "mobile" },
                    ],
                    content: contents
                }]
                
                const settings = {
                    fileName: "Customers CRM - Optik Melawai", // Name of the resulting spreadsheet
                    extraLength: 3, // A bigger number means that columns will be wider
                    writeMode: "writeFile", // The available parameters are 'WriteFile' and 'write'. This setting is optional. Useful in such cases https://docs.sheetjs.com/docs/solutions/output#example-remote-file
                    writeOptions: {}, // Style options from https://docs.sheetjs.com/docs/api/write-options
                    RTL: false, // Display the columns from right-to-left (the default value is false)
                }

                xlsx(exportData, settings)
            },
            async handleImport() {
                const input = document.getElementById('import-customer')
                if (input.files.length < 1) {
                    return alert('File is required')
                }

                await readXlsxFile(input.files[0]).then(rows => {
                    for (let i = 0; i < rows.length; i++)
                    {
                        if (i === 0) {
                            const headers = ["Name", "BirthDate", "Address", "CustomerCity", "MobilePhone", "Email"]
                            const result = rows[0]?.filter((header, i) => header === headers[i] ? true : false )
                            if (result.length !== 6) {
                                alert('Template excel tidak sesuai, mohon download template excel terlebih dahulu')
                                break
                            }
                            continue
                        }

                        sendRequest('POST', `${import.meta.env.VITE_APP_BACKEND_HOST}/api/v1/customers`, {
                            "customerName": rows[i][0],
                            "customerBirthDate": rows[i][1],
                            "customerAddress": rows[i][2],
                            "customerCity": rows[i][3],
                            "customerNoHandphone": rows[i][4],
                            "customerEmail": rows[i][5]
                        })
                        .then(res => {
                            console.log(res)
                        })
                        .catch(err => {
                            console.log(err)
                        })
                    }
                    alert('Import customers success')
                })
                
                this.handleCustomers()
                this.dialog = false
            },
            handleSearch(e) {
                if (e.keyCode === 13) {
                    this.handleCustomers(`${import.meta.env.VITE_APP_BACKEND_HOST}/api/v1/customers?search=${e.target.value}`)
                } 
            }
        }
    }
</script>