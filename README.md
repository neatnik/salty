# Salty

Portable NaCl-powered encryption

Salty makes it easy to send strongly-encrypted messages with a shared key. It uses [NaCl](https://nacl.cr.yp.to) (via [Libsodium](https://download.libsodium.org/doc/)) for encryption and [basE91](http://base91.sourceforge.net) for portability.

With Salty, you can encrypt a message as long as 185 characters and the resulting cipher will still fit in a tweet (~277 characters), making it ideal for encrypting tweets or other length-restricted communication. You can use it anywhere, though, with text of any length.

## Demo

You can try it out at https://neatnik.net/salty/

## Examples

Unencrypted payload: `The quick brown fox jumped over the lazy sleeping dog.`

Key: `hunter2`

Resulting Salty cipher:

```
-- BEGIN SALTY ENCRYPTED MESSAGE --
WZ {/ rf 4a aQ 8f tC WI c? VJ nK UQ 
>T 7W nj W7 rR r~ r& :. zY NJ sm k6 
`@ eq G5 Ty Tl uE %T uR AM D_ J~ "Y 
p+ q2 AM dN 0} ;H #v Ez L_ 9m }! X^ 
Ws `v %) >v ,_ ^] 70 ,+ hv TN
-- END SALTY ENCRYPTED MESSAGE --
```

(Note that the cipher will change with each encryption.)

The above cipher is identical to this shortened version:

```
RX.c:L6%xUa,Rhg>w%@]X+rl|a4{uPVRa.)
;&wSOD+_(kJ=bZ?&_|*z+se035=Dw*2Rl?(
H&0c{~5i@CT!V&m5O4&BHNcEL:%c5Tbsd9n
#8++h/*YsGP
```

Using the key above on either cipher will yield the same plaintext message. Salty’s shortened format is ideal for space-restricted contexts (e.g. Twitter), whereas the longer format works better in emails or other places where text might need to freely wrap.

## Spec ##

Salty’s spec is uncomplicated:

* Take a plaintext message and encrypt it via NaCl’s secret key authenticated encryption scheme “crypto_secretbox”.
* Then take the resulting binary data and encode it in basE91. (Why basE91? It’s the most efficient base conversion around, making the most effective use of available ASCII characters.)
* The resulting encoded cipher can be used as is, or wrapped in the BEGIN header and END footer, and spaces can be added to make the cipher wrap nicely in different places (e.g. email).
* When decrypting a cipher, first remove any spaces or newline characters, as well as the optional BEGIN/END header/footer. The resulting basE91-decoded cipher is ready for decryption via NaCl, using the same key and salt used during encryption.

## Further Reading

* [NaCl: Networking and Cryptography library](https://nacl.cr.yp.to)
* [basE91](http://base91.sourceforge.net)
* [Libsodium](https://download.libsodium.org/doc/)