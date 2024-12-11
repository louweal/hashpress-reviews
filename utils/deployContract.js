require('dotenv').config();

var fs = require('fs');

const { Client, AccountId, PrivateKey, ContractCreateFlow } = require('@hashgraph/sdk');

const args = process.argv.slice(2);

async function main() {
    const operatorId = AccountId.fromString(process.env.MY_ACCOUNT_ID);
    const operatorKey = PrivateKey.fromString(process.env.MY_PRIVATE_KEY);

    let client;
    if (args[0] == 'mainnet') {
        client = Client.forMainnet().setOperator(operatorId, operatorKey);
    } else {
        client = Client.forTestnet().setOperator(operatorId, operatorKey);
    }

    // const contractName = "Storage";
    const contractName = 'Review';

    const bytecode = fs.readFileSync(`./contracts/${contractName}_sol_${contractName}.bin`);

    const createContract = new ContractCreateFlow()
        .setGas(150000) // Increase if revert
        .setBytecode(bytecode); // Contract bytecode
    const createContractTx = await createContract.execute(client);
    const createContractRx = await createContractTx.getReceipt(client);
    const contractId = createContractRx.contractId;

    // console.log(`New ${contractName} contract created with ID: ${contractId}`);

    process.exit();
}

main();
